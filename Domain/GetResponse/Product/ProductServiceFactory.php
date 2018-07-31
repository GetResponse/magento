<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Product;

use GrShareCode\DbRepositoryInterface;
use GrShareCode\GetresponseApi;
use GrShareCode\Product\ProductService as GrProductService;

/**
 * Class ProductServiceFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Product
 */
class ProductServiceFactory
{
    /** @var GetresponseApi */
    private $getResponseApi;

    /** @var DbRepositoryInterface */
    private $repository;

    /**
     * @param GetresponseApi $getResponseApi
     * @param DbRepositoryInterface $repository
     */
    public function __construct(GetresponseApi $getResponseApi, DbRepositoryInterface $repository)
    {
        $this->getResponseApi = $getResponseApi;
        $this->repository = $repository;
    }

    /**
     * @return GrProductService
     */
    public function create()
    {
        return new GrProductService(
            $this->getResponseApi,
            $this->repository
        );
    }
}