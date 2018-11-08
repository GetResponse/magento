<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\AddOrderCommandFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\ApiTypeException;
use GrShareCode\Contact\ContactCustomField;
use GrShareCode\Contact\ContactCustomFieldsCollection;
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

    /** @var GetresponseApiClientFactory */
    private $apiClientFactory;

    /** @var CartService */
    private $cartService;

    /** @var OrderService */
    private $orderService;

    /** @var AddOrderCommandFactory */
    private $addOrderCommandFactory;

    /** @var ContactService */
    private $contactService;

    /** @var array */
    private $customsMapping;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param CartService $cartService
     * @param OrderService $orderService
     * @param AddOrderCommandFactory $addOrderCommandFactory
     * @param ContactService $contactService
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        GetresponseApiClientFactory $apiClientFactory,
        CartService $cartService,
        OrderService $orderService,
        AddOrderCommandFactory $addOrderCommandFactory,
        ContactService $contactService
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->apiClientFactory = $apiClientFactory;
        $this->repository = $repository;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->addOrderCommandFactory = $addOrderCommandFactory;
        $this->contactService = $contactService;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
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
        } catch (RequestValidationException $e) {
            return $this->handleException($e);
        } catch (CustomFieldFactoryException $e) {
            return $this->handleException($e);
        }

        $contactListId = $data['campaign_id'];
        $subscribers = $this->repository->getFullCustomersDetails();

        try {
            $this->customsMapping = $this->prepareCustomsMapping($customs);

            /** @var Subscriber $subscriber */
            foreach ($subscribers as $subscriber) {

                $this->stats['count']++;
                $custom_fields = [];
                foreach ($customs as $field => $name) {
                    if (!empty($subscriber[$field])) {
                        $custom_fields[$name] = $subscriber[$field];
                    }
                }

                $dayOfCycle = (isset($data['gr_autoresponder']) && $data['cycle_day'] != '') ? (int)$data['cycle_day'] : null;


                try {
                    $this->upsertContact(
                        $contactListId,
                        $subscriber['firstname'],
                        $subscriber['lastname'],
                        $subscriber['subscriber_email'],
                        $dayOfCycle,
                        $custom_fields
                    );
                } catch (GetresponseApiException $e) {
                    continue;
                }

                if (empty($data['ecommerce'])) {
                    continue;
                }

                /** @var Order $order */
                foreach ($this->repository->getOrderByCustomerId($subscriber->getCustomerId()) as $order) {
                    $grShopId = $data['store_id'];
                    try {
                        $this->orderService->exportOrder(
                            $this->addOrderCommandFactory->createForOrderService(
                                $order, $contactListId, $grShopId
                            )
                        );
                    } catch (\Exception $e) {
                        $this->handleException($e);
                    }
                }
            }

        } catch (RepositoryException $e) {
            return $this->handleException($e);
        } catch (ConnectionSettingsException $e) {
            return $this->handleException($e);
        } catch (ApiTypeException $e) {
            return $this->handleException($e);
        } catch (GetresponseApiException $e) {
            return $this->handleException($e);
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

        if (!empty($data['gr_autoresponder']) && (strlen($data['cycle_day']) === 0)) {
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
     * @param string $campaignId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $cycleDay
     * @param array $userCustoms
     * @throws GetresponseApiException
     * @throws ConnectionSettingsException
     * @throws ApiTypeException
     */
    private function upsertContact($campaignId, $firstName, $lastName, $email, $cycleDay = null, $userCustoms = [])
    {
        $customFields = new ContactCustomFieldsCollection();

        foreach ($userCustoms as $name => $value) {
            if (!empty($this->customsMapping[$name])) {
                $customFields->add(new ContactCustomField($this->customsMapping[$name], $value));
            }
        }

        $this->contactService->upsertContact(
            $email,
            $firstName,
            $lastName,
            $campaignId,
            $cycleDay,
            $customFields
        );

        $this->stats['added']++;
    }

    /**
     * @param $customs
     * @return array
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    private function prepareCustomsMapping($customs)
    {
        $mapping = [];
        $apiClient = $this->apiClientFactory->createGetResponseApiClient();

        foreach ($customs as $name => $grCustomName) {

            $custom = $apiClient->getCustomFieldByName($grCustomName);

            if (!empty($custom)) {
                $mapping[$name] = $custom['customFieldId'];
            }
        }

        return $mapping;
    }

}
