<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomField;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService;
use GrShareCode\GetresponseApiClient;
use GrShareCode\GetresponseApiException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class Process
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Process extends AbstractController
{
    const PAGE_TITLE = 'Export Customer Data on Demand';

    public $stats = [
        'count' => 0,
        'added' => 0,
        'updated' => 0,
        'error' => 0
    ];

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    /** @var GetresponseApiClient */
    private $grApiClient;

    /** @var CartService */
    private $cartService;

    /** @var OrderService */
    private $orderService;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     * @param CartService $cartService
     * @param OrderService $orderService
     * @throws RepositoryException
     * @throws ApiTypeException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator,
        CartService $cartService,
        OrderService $orderService
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->resultPageFactory = $resultPageFactory;
        $this->grApiClient = $repositoryFactory->createGetResponseApiClient();
        $this->repository = $repository;
        $this->cartService = $cartService;
        $this->orderService = $orderService;

        return $this->checkGetResponseConnection();
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws GetresponseApiException
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        try {
            $this->validateRequest();

            if (isset($data['gr_sync_order_data'])) {
                $customs = CustomFieldFactory::createFromArray($data);
            } else {
                $customs = [];
            }

            $contactListId = $data['campaign_id'];

        } catch (RequestValidationException $e) {
            return $this->handleException($e);
        } catch (CustomFieldFactoryException $e) {
            return $this->handleException($e);
        }

        $subscribers = $this->repository->getFullCustomersDetails();

        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {

            $this->stats['count']++;
            $custom_fields = [];
            foreach ($customs as $field => $name) {
                if (!empty($customer[$field])) {
                    $custom_fields[$name] = $customer[$field];
                }
            }

            $cycle_day = (isset($data['gr_autoresponder']) && $data['cycle_day'] != '') ? (int)$data['cycle_day'] : 0;

            $this->addContact(
                $contactListId,
                $subscriber['firstname'],
                $subscriber['lastname'],
                $subscriber['subscriber_email'],
                $cycle_day,
                $custom_fields
            );

            if (empty($data['ecommerce'])) {
                continue;
            }

            /** @var Order $order */
            foreach ($this->repository->getOrderByCustomerId($subscriber->getCustomerId()) as $order) {
                $grShopId = $data['store_id'];
                try {
                    $this->orderService->exportOrder($order, $contactListId, $grShopId);
                } catch (\Exception $e) {
                    $this->handleException($e);
                }
            }
        }

        $this->messageManager->addSuccessMessage(Message::DATA_EXPORTED);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }

    /**
     * @throws RequestValidationException
     */
    private function validateRequest()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data['campaign_id'])) {
            throw RequestValidationException::createWithMessage(Message::SELECT_CONTACT_LIST);
        }

        if (!empty($data['gr_autoresponder']) && empty($data['cycle_day'])) {
            throw RequestValidationException::createWithMessage(Message::SELECT_AUTORESPONDER_DAY);
        }

        if (!empty($data['ecommerce']) && empty($data['store_id'])) {
            throw RequestValidationException::createWithMessage(Message::STORE_CHOOSE);
        }
    }

    /**
     * @param \Exception $e
     * @return Page
     */
    private function handleException(\Exception $e)
    {
        $this->messageManager->addErrorMessage($e->getMessage());
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }

    /**
     * Add (or update) contact to gr campaign
     *
     * @param string $campaignId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $cycleDay
     * @param array $userCustoms
     * @throws GetresponseApiException
     */
    public function addContact($campaignId, $firstName, $lastName, $email, $cycleDay = null, $userCustoms = [])
    {
        $customFields = new ContactCustomFieldsCollection();

        foreach ($userCustoms as $name => $value) {
            $custom = $this->grApiClient->getCustomFieldByName($name);

            if (!empty($custom)) {
                $customFields->add(new ContactCustomField($custom['customFieldId'], $value));
            }
        }

        $name = trim($firstName . ' ' . $lastName);

        $service = new ContactService($this->grApiClient);
        $service->upsertContact(new AddContactCommand(
            $email,
            !empty($name) ? $name : null,
            $campaignId,
            $cycleDay,
            $customFields,
            Config::ORIGIN_NAME
        ));

        $this->stats['added']++;
    }

}
