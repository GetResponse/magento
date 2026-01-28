<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use RuntimeException;

abstract class ApiController
{
    /** @var Scope */
    protected $scope;
    /** @var MagentoStore */
    protected $magentoStore;
    /** @var Repository */
    protected $repository;

    public function __construct(Repository $repository, MagentoStore $magentoStore)
    {
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    /**
     * This method initializes properties used in controllers.
     * @throws WebapiException
     */
    public function verifyScope(int $scope): void
    {
        if (empty($scope)) {
            throw new WebapiException(new Phrase('Missing scope.'));
        }

        if (!$this->magentoStore->storeExists((int)$scope)) {
            throw new WebapiException(new Phrase('Incorrect scope.'));
        }

        $this->scope = Scope::createFromStoreId($scope);
    }

    /**
     * @throws Exception
     * @return void
     */
    public function execute(): void
    {
        throw new RuntimeException('Method not implemented.');
    }
}
