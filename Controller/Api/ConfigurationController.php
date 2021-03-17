<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\FacebookPixel;
use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTracking;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Module\ModuleListInterface;

/**
 * @api
 */
class ConfigurationController extends ApiController
{
    const MODULE_NAME = 'GetResponse_GetResponseIntegration';

    private $moduleList;

    public function __construct(Context $context, ModuleListInterface $moduleList)
    {
        parent::__construct($context);

        $this->moduleList = $moduleList;

        $this->initialize();
        $this->verifyPluginMode();
    }

    /**
     * @return array
     */
    public function list(): array
    {
        $versionInfo = $this->moduleList->getOne(self::MODULE_NAME);
        $pluginVersion = $versionInfo['setup_version'] ?? '';

        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($this->scope->getScopeId()));

        $facebookPixel = FacebookPixel::createFromRepository(
            $this->repository->getFacebookPixelSnippet($this->scope->getScopeId())
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
     * @return void
     */
    public function update()
    {
        $this->repository->saveFacebookPixelSnippet(
            FacebookPixel::createFromRequest($this->request->getBodyParams()),
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
    }
}
