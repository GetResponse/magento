<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Block\Settings;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomsFactory;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Process
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Process extends Action
{
    protected $resultPageFactory;

    /** @var ApiHelper */
    private $apiHelper;

    /** @var GetResponseAPI3 */
    public $grApi;

    public $stats = [
        'count'      => 0,
        'added'      => 0,
        'updated'    => 0,
        'error'      => 0
    ];

    /**
     * Process constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var Settings $block */
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $data = $this->getRequest()->getPostValue();

        $campaign = $data['campaign_id'];
        if (empty($campaign)) {
            $this->messageManager->addErrorMessage('You need to select contact list');
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
            $resultPage->getConfig()->getTitle()->prepend('Export Customer Data on Demand');

            return $resultPage;
        }

        if (isset($data['gr_sync_order_data'])) {
            $customs = CustomsFactory::buildFromFormPayload($data);
        } else {
            $customs = [];
        }

        foreach ($customs as $field => $name) {
            if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                $this->messageManager->addErrorMessage('There is a problem with one of your custom field name! Field name
                must be composed using up to 32 characters, only a-z (lower case), numbers and "_".');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
                $resultPage->getConfig()->getTitle()->prepend('Export Customer Data on Demand');

                return $resultPage;
            }
        }

        // only those that are subscribed to newsletters
        $customers = $block->getCustomers();
        $this->grApi = $block->getClient();

        if (empty($this->grApi)) {
            return;
        }

        $this->apiHelper = new ApiHelper($this->grApi);

        foreach ($customers as $customer) {
            $customer = $customer->getData();
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
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
        $resultPage->getConfig()->getTitle()->prepend('Export Customer Data on Demand');

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

        $results = (array) $this->grApi->getContacts([
            'query' => [
                'email'      => $email,
                'campaignId' => $campaign
            ]
        ]);

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->grApi->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->apiHelper->mergeUserCustoms($results->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            }
            $response = $this->grApi->updateContact($contact->contactId, $params);
            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['updated']++;
            }
            return $response;
        } else {
            $params['customFieldValues'] = $this->apiHelper->setCustoms($user_customs);
            $response = $this->grApi->addContact($params);
            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['added']++;
            }
            return $response;
        }
    }
}