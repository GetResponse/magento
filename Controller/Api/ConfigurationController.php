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
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;
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
     * @throws WebapiException
     * @return ConfigurationPresenter
     */
    public function list(string $scope): ConfigurationPresenter
    {
        $this->verifyScope($scope);

        $versionInfo = $this->moduleList->getOne(self::MODULE_NAME);
        $pluginVersion = $versionInfo['setup_version'] ?? '';

        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());

        $facebookPixel = FacebookPixel::createFromRepository(
            $this->repository->getFacebookPixelSnippet($this->scope->getScopeId())
        );

        $facebookAdsPixel = FacebookAdsPixel::createFromRepository(
            $this->repository->getFacebookAdsPixelSnippet($this->scope->getScopeId())
        );

        $facebookBusinessExtension = FacebookBusinessExtension::createFromRepository(
            $this->repository->getFacebookBusinessExtensionSnippet($this->scope->getScopeId())
        );

        $webForm = WebForm::createFromRepository(
            $this->repository->getWebformSettings($this->scope->getScopeId())
        );

        $webEventTracking = WebEventTracking::createFromRepository(
            $this->repository->getWebEventTracking($this->scope->getScopeId())
        );

        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($this->scope->getScopeId())
        );

        return new ConfigurationPresenter(
            $pluginVersion,
            $pluginMode,
            $this->scope,
            $facebookPixel,
            $facebookAdsPixel,
            $facebookBusinessExtension,
            $webForm,
            $webEventTracking,
            $liveSynchronization
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
}
