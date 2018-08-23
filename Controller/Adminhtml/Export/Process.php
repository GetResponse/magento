<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Order\OrderService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
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

    /** @var GrRepository */
    private $grRepository;

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
        $this->grRepository = $repositoryFactory->createRepository();
        $this->repository = $repository;
        $this->cartService = $cartService;
        $this->orderService = $orderService;

        return $this->checkGetResponseConnection();
    }

    /**
     * @return ResponseInterface|Page
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
                    $this->cartService->exportCart($order->getQuoteId(), $contactListId, $grShopId);
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
