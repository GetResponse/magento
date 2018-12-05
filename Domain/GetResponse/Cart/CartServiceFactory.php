<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeCache;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GrShareCode\Cart\CartService as GrCartService;
use GrShareCode\Cart\CartServiceFactory as GrCartServiceFactory;

/**
 * Class CartServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Cart
 */
class CartServiceFactory
{
    /** @var ShareCodeRepository */
    private $shareCodeRepository;

    /** @var ShareCodeCache */
    private $shareCodeCache;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var GrCartServiceFactory */
    private $cartServiceFactory;

    /**
     * @param ShareCodeRepository $shareCodeRepository
     * @param ShareCodeCache $shareCodeCache
     * @param ApiClientFactory $apiClientFactory
     * @param GrCartServiceFactory $cartServiceFactory
     */
    public function __construct(
        ShareCodeRepository $shareCodeRepository,
        ShareCodeCache $shareCodeCache,
        ApiClientFactory $apiClientFactory,
        GrCartServiceFactory $cartServiceFactory
    ) {
        $this->shareCodeRepository = $shareCodeRepository;
        $this->shareCodeCache = $shareCodeCache;
        $this->apiClientFactory = $apiClientFactory;
        $this->cartServiceFactory = $cartServiceFactory;
    }

    /**
     * @return GrCartService
     * @throws ApiException
     */
    public function create()
    {
        $getResponseApiClient = $this->apiClientFactory->createGetResponseApiClient();

        return $this->cartServiceFactory->create(
            $getResponseApiClient,
            $this->shareCodeRepository,
            $this->shareCodeCache
        );
    }
}
