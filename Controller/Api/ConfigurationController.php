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

    /**
     * @param Repository $repository
     * @param MagentoStore $magentoStore
     * @param ModuleListInterface $moduleList
     * @param Request $request
     * @throws WebapiException
     */
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
     * @param string $scope
     * @return array
     * @throws WebapiException
     */
    public function list(string $scope): array
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

        return [
            [
                'general' => [
                    'plugin_version' => $pluginVersion,
                    'mode' => $pluginMode->getMode(),
                    'scope' => $this->scope->getScopeId(),
                ],
                'sections' => [
                    'facebookPixel' => [
                        'enabled' => $facebookPixel->isActive(),
                        'snippet' => $facebookPixel->getCodeSnippet()
                    ],
                    'facebookAdsPixel' => [
                        'enabled' => $facebookAdsPixel->isActive(),
                        'snippet' => $facebookAdsPixel->getCodeSnippet()
                    ],
                    'facebookBusinessExtension' => [
                        'enabled' => $facebookBusinessExtension->isActive(),
                        'snippet' => $facebookBusinessExtension->getCodeSnippet()
                    ],
                    'webforms' => [
                        'enabled' => $webForm->isEnabled(),
                        'form_id' => $webForm->getWebFormId(),
                        'url' => $webForm->getUrl(),
                        'block_id' => $webForm->getSidebar()
                    ],
                    'web_event_tracking' => [
                        'enabled' => $webEventTracking->isEnabled(),
                        'snippet' => $webEventTracking->getCodeSnippet(),
                        'isFeatureEnabled' => $webEventTracking->isFeatureTrackingEnabled(),
                    ],
                    'live_synchronization' => $liveSynchronization->isActive(),
                    'callbackUrl' => $liveSynchronization->getCallbackUrl()
                ]

            ]
        ];
    }

    /**
     * @param string $scope
     * @return void
     * @throws WebapiException
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
