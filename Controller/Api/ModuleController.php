<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use Magento\Backend\App\Action\Context;

/**
 * @api
 */
class ModuleController extends ApiController
{
    const MODE_PARAM = 'mode';

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->initialize();
    }

    /**
     * @return void
     */
    public function switch()
    {
        try {
            $newMode = $this->request->getBodyParams()[self::MODE_PARAM] ?? '';
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($this->scope->getScopeId()));

            $pluginMode->switch($newMode);
            $this->repository->savePluginMode($pluginMode, $this->scope->getScopeId());
            return null;
        } catch (PluginModeException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
