<?php
namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Cart\CartService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CreateCartHandler
 * @package GetResponse\GetResponseIntegration\Observer
 */
class CreateCartHandler extends Ecommerce implements ObserverInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var CartService */
    private $cartService;

    /** @var Repository */
    private $magentoRepository;

    /** @var Logger */
    private $logger;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Repository $repository
     * @param CartService $cartService
     * @param ContactService $contactService
     * @param Logger $getResponseLogger
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Repository $repository,
        CartService $cartService,
        ContactService $contactService,
        Logger $getResponseLogger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->magentoRepository = $repository;
        $this->cartService = $cartService;
        $this->logger = $getResponseLogger;

        parent::__construct(
            $objectManager,
            $customerSession,
            $repository,
            $contactService
        );
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        try {

            if (!$this->canHandleECommerceEvent()) {
                return $this;
            }

            $shopId = $this->scopeConfig->getValue(Config::CONFIG_DATA_SHOP_ID);

            if (empty($shopId)) {
                return $this;
            }

            $contactListId = $this->magentoRepository->getRegistrationSettings()['campaignId'];

            $this->cartService->sendCart(
                $observer->getCart()->getQuote()->getId(),
                $contactListId,
                $shopId
            );

        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $this;
    }
}
