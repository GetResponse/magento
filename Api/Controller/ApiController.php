<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use Exception;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

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
     * @param string $scope
     * @return void
     * @throws WebapiException
     */
    public function verifyScope(string $scope): void
    {
        if (empty($scope)) {
            throw new WebapiException(new Phrase('Missing scope.'));
        }

        if (!$this->magentoStore->storeExists((int)$scope)) {
            throw new WebapiException(new Phrase('Incorrect scope.'));
        }

        $this->scope = new Scope($scope);
    }

    /**
     * @throws WebapiException
     * @return void
     */
    public function verifyPluginMode(): void
    {
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

        if (!$pluginMode->isNewVersion()) {
            throw new WebapiException(new Phrase('Incorrect plugin mode'));
        }
    }

    /**
     * @throws Exception
     * @return void
     */
    public function execute(): void
    {
        throw new Exception('Method not implemented.');
    }
}
