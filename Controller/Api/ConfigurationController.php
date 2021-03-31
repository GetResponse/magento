<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RequestValidationException;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\General;
use GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\Store;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * @api
 */
class ConfigurationController extends ApiController
{
    private const MODULE_NAME = 'GetResponse_GetResponseIntegration';

    private $moduleList;
    private $request;

    public function __construct(
        Repository $repository,
        MagentoStore $magentoStore,
        ModuleListInterface $moduleList,
        Request $request
    ) {
        parent::__construct($repository, $magentoStore);
        $this->moduleList = $moduleList;
        $this->request = $request;
    }

    /**
     * @return ConfigurationPresenter
     */
    public function list(): ConfigurationPresenter
    {
        $versionInfo = $this->moduleList->getOne(self::MODULE_NAME);
        $pluginVersion = $versionInfo['setup_version'] ?? '';

        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
        $stores = [];

        foreach ($this->magentoStore->getMagentoStores() as $storeId => $storeName) {
            $scope = new Scope($storeId);
            $stores[] = $pluginMode->isNewVersion() ? $this->createStore($scope) : $this->createEmptyStoreConfiguration($scope);
        }

        return new ConfigurationPresenter(
            new General($pluginVersion, $pluginMode),
            $stores
        );
    }

    /**
     * @throws WebapiException
     * @return void
     * @param string $scope
     */
    public function update(string $scope): void
    {
        $this->verifyPluginMode();
        $this->verifyScope($scope);

        try {
            $this->repository->saveFacebookPixelSnippet(
                FacebookPixel::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );

            $this->repository->saveFacebookAdsPixelSnippet(
                FacebookAdsPixel::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );

            $this->repository->saveFacebookBusinessExtensionSnippet(
                FacebookBusinessExtension::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );

            $this->repository->saveWebformSettings(
                WebForm::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );

            $this->repository->saveWebEventTracking(
                WebEventTracking::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );

            $this->repository->saveLiveSynchronization(
                LiveSynchronization::createFromRequest($this->request->getBodyParams()),
                $this->scope->getScopeId()
            );
        } catch (RequestValidationException $e) {
            throw new WebapiException(new Phrase($e->getMessage()));
        }
    }

    private function createStore(Scope $scope): Store
    {
        return new Store(
            $scope,
            FacebookPixel::createFromRepository(
                $this->repository->getFacebookPixelSnippet($scope->getScopeId())
            ),
            FacebookAdsPixel::createFromRepository(
                $this->repository->getFacebookAdsPixelSnippet($scope->getScopeId())
            ),
            FacebookBusinessExtension::createFromRepository(
                $this->repository->getFacebookBusinessExtensionSnippet($scope->getScopeId())
            ),
            WebForm::createFromRepository(
                $this->repository->getWebformSettings($scope->getScopeId())
            ),
            WebEventTracking::createFromRepository(
                $this->repository->getWebEventTracking($scope->getScopeId())
            ),
            LiveSynchronization::createFromRepository(
                $this->repository->getLiveSynchronization($scope->getScopeId())
            )
        );
    }

    private function createEmptyStoreConfiguration(Scope $scope): Store
    {
        return new Store(
            $scope,
            new FacebookPixel(false, ''),
            new FacebookAdsPixel(false, ''),
            new FacebookBusinessExtension(false, ''),
            new WebForm(false, '', '', ''),
            new WebEventTracking(false, false, ''),
            new LiveSynchronization(false, '')
        );
    }
}
