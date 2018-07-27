<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Message;
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
class Process extends AbstractController
{
    const PAGE_TITLE = 'Export Customer Data on Demand';
    const SUBSCRIBED = 1;

    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    public $stats = [
        'count' => 0,
        'added' => 0,
        'updated' => 0,
        'error' => 0
    ];

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->resultPageFactory = $resultPageFactory;
        $this->grRepository = $repositoryFactory->createRepository();
        $this->repository = $repository;

        return $this->checkGetResponseConnection();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Page
     */
    public function execute()
    {
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
            $this->messageManager->addErrorMessage(Message::SELECT_CONTACT_LIST);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        if (isset($data['gr_sync_order_data'])) {
            $customs = CustomFieldFactory::createFromArray($data);
        } else {
            $customs = [];
        }

        foreach ($customs as $field => $name) {
            if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                $this->messageManager->addErrorMessage(sprintf(Message::INVALID_CUSTOM_FIELD_VALUE, $name));
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

                return $resultPage;
            }
        }

        $subscribers = $this->repository->getFullCustomersDetails();

        foreach ($subscribers as $subscriber) {

            $this->stats['count']++;
            $custom_fields = [];
            foreach ($customs as $field => $name) {
                if (!empty($customer[$field])) {
                    $custom_fields[$name] = $customer[$field];
                }
            }
            $custom_fields['origin'] = 'magento2';
            $cycle_day = (isset($data['gr_autoresponder']) && $data['cycle_day'] != '') ? (int)$data['cycle_day'] : 0;

            $this->addContact(
                $campaign,
                $subscriber['firstname'],
                $subscriber['lastname'],
                $subscriber['subscriber_email'],
                $cycle_day,
                $custom_fields
            );
        }

        $this->messageManager->addSuccessMessage(Message::DATA_EXPORTED);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }


    /**
     * Add (or update) contact to gr campaign
     *
     * @param string $campaign
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $cycleDay
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstName, $lastName, $email, $cycleDay = 0, $user_customs = [])
    {
        $apiHelper = new ApiHelper($this->grRepository);

        $user_customs['origin'] = 'magento2';

        $params = [
            'email' => $email,
            'campaign' => ['campaignId' => $campaign],
            'ipAddress' => $_SERVER['REMOTE_ADDR']
        ];

        if (!empty($firstName) || !empty($lastName)) {
            $params['name'] = trim($firstName) . ' ' . trim($lastName);
        }

        if (!empty($cycleDay)) {
            $params['dayOfCycle'] = (int)$cycleDay;
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
