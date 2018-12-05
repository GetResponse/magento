<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\DbRepositoryInterface;
use GrShareCode\Product\ProductService;

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
     * @return ProductService
     */
    public function create()
    {
        return new ProductService(
            $this->getResponseApiClient,
            $this->repository
        );
    }
}