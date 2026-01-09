<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Api\PlatformVersionProvider;
use GetResponse\GetResponseIntegration\Controller\Api\ConfigurationControllerInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookAdsPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookBusinessExtension;
use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RequestValidationException;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\General;
use GetResponse\GetResponseIntegration\Presenter\Api\Section\Store;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * @api
 */
class ConfigurationController extends ApiController implements ConfigurationControllerInterface
{
    private $request;
    private $cacheManager;
    /** @var PlatformVersionProvider */
    private $platformVersionProvider;

    public function __construct(
        Repository $repository,
        MagentoStore $magentoStore,
        Request $request,
        Manager $cacheManager,
        PlatformVersionProvider $platformVersionProvider
    ) {
        parent::__construct($repository, $magentoStore);
        $this->request = $request;
        $this->cacheManager = $cacheManager;
        $this->platformVersionProvider = $platformVersionProvider;
    }

    /**
     * @return ConfigurationPresenter
     */
    public function list(): ConfigurationPresenter
    {
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
        $stores = [];

        foreach ($this->magentoStore->getMagentoStores() as $storeId => $storeName) {
            $scope = new Scope($storeId);
            $stores[] = $pluginMode->isNewVersion()
                ? $this->createStore($scope)
                : $this->createEmptyStoreConfiguration($scope);
        }

        return new ConfigurationPresenter(
            new General(
                $this->platformVersionProvider->getPluginVersion(),
                $this->platformVersionProvider->getMagentoVersion(),
                $this->platformVersionProvider->getPhpVersion(),
                $pluginMode
            ),
            $stores
        );
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        foreach ($this->magentoStore->getMagentoStores() as $storeId => $storeName) {
            $this->repository->clearConfiguration($storeId);
        }

        $this->clearCache();
    }

    /**
     * @throws WebapiException
     * @return void
     * @param string $scope
     */
    public function update(string $scope): void
    {
        try {
            $this->verifyScope($scope);

            $requestBody = $this->request->getBodyParams();

            $facebookPixel = FacebookPixel::createFromRequest($requestBody);
            $facebookAdsPixel = FacebookAdsPixel::createFromRequest($requestBody);
            $facebookBusinessExtension = FacebookBusinessExtension::createFromRequest($requestBody);
            $webForm = WebForm::createFromRequest($requestBody);
            $webEventTracking = WebEventTracking::createFromRequest($requestBody);
            $liveSynchronization = LiveSynchronization::createFromRequest($requestBody);

            $this->repository->saveFacebookPixelSnippet($facebookPixel, $scope);
            $this->repository->saveFacebookAdsPixelSnippet($facebookAdsPixel, $scope);
            $this->repository->saveFacebookBusinessExtensionSnippet($facebookBusinessExtension, $scope);
            $this->repository->saveWebformSettings($webForm, $scope);
            $this->repository->saveWebEventTracking($webEventTracking, $scope);
            $this->repository->saveLiveSynchronization($liveSynchronization, $scope);

            $this->clearCache();
            $this->cacheManager->clean(['config']);
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

    private function clearCache(): void
    {
        $this->cacheManager->clean(['full_page', 'config']);
    }
}
