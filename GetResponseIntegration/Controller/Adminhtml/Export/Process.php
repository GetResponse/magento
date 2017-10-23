<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Process
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Process extends Action
{
    const PAGE_TITLE = 'Export Customer Data on Demand';

    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    public $stats = [
        'count'      => 0,
        'added'      => 0,
        'updated'    => 0,
        'error'      => 0
    ];

    /** @var GrRepository */
    private $grRepository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->grRepository = $repositoryFactory->buildRepository();
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Page
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        $campaign = $data['campaign_id'];

        if (empty($campaign)) {
            $this->messageManager->addErrorMessage('You need to select contact list');
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        if (isset($data['gr_sync_order_data'])) {
            $customs = CustomFieldFactory::buildFromUserPayload($data);
        } else {
            $customs = [];
        }

        foreach ($customs as $field => $name) {
            if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                $this->messageManager->addErrorMessage('There is a problem with one of your custom field name! Field name
                must be composed using up to 32 characters, only a-z (lower case), numbers and "_".');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
                return $resultPage;
            }
        }

        // only those that are subscribed to newsletters
        $customers = $this->repository->getFullCustomersDetails();

        foreach ($customers as $customer) {
            // create contact factory
            $this->stats['count']++;
            $custom_fields = [];
            foreach ($customs as $field => $name) {
                if (!empty($customer[$field])) {
                    $custom_fields[$name] = $customer[$field];
                }
            }
            $custom_fields['origin'] = 'magento2';
            $cycle_day = (isset($data['gr_autoresponder']) && $data['cycle_day'] != '') ? (int) $data['cycle_day'] : 0;

            $this->addContact($campaign, $customer['firstname'], $customer['lastname'], $customer['email'], $cycle_day, $custom_fields);
        }

        $this->messageManager->addSuccessMessage('Customer data exported');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
        return $resultPage;
    }


    /**
     * Add (or update) contact to gr campaign
     *
     * @param       $campaign
     * @param       $firstname
     * @param       $lastname
     * @param       $email
     * @param int   $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstname, $lastname, $email, $cycle_day = 0, $user_customs = [])
    {
        $apiHelper = new ApiHelper($this->grRepository);
        $name = trim($firstname) . ' ' . trim($lastname);

        $user_customs['origin'] = 'magento2';

        $params = [
            'name'       => $name,
            'email'      => $email,
            'campaign'   => ['campaignId' => $campaign],
            'ipAddress'  => $_SERVER['REMOTE_ADDR']
        ];

        if (!empty($cycle_day)) {
            $params['dayOfCycle'] = (int) $cycle_day;
        }

        $contact = $this->grRepository->getContactByEmail($email, $campaign);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            if (!empty($contact->customFieldValues)) {
                $params['customFieldValues'] = $apiHelper->mergeUserCustoms($contact->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);
            }
            $response = $this->grRepository->updateContact($contact->contactId, $params);
            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['updated']++;
            }
            return $response;
        } else {
            $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);

            $response = $this->grRepository->addContact($params);

            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['added']++;
            }
            return $response;
        }
    }
}
