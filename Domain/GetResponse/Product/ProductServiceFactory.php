<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GrShareCode\DbRepositoryInterface;
use GrShareCode\GetresponseApiClient;
use GrShareCode\Product\ProductService as GrProductService;

/**
 * Class ProductServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductServiceFactory
{
    /** @var GetresponseApiClient */
    private $getResponseApiClient;

    /** @var DbRepositoryInterface */
    private $repository;

    /**
     * @param GetresponseApiClient $getResponseApiClient
     * @param DbRepositoryInterface $repository
     */
    public function __construct(GetresponseApiClient $getResponseApiClient, DbRepositoryInterface $repository)
    {
        $this->getResponseApiClient = $getResponseApiClient;
        $this->repository = $repository;
    }

    /**
     * @return GrProductService
     */
    public function create()
    {
        return new GrProductService(
            $this->getResponseApiClient,
            $this->repository
        );
    }
}