<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use Magento\Backend\App\Action\Context;

/**
 * @api
 */
class Module extends ApiAbstractController
{
    const MODE_PARAM = 'mode';
    const PAGE = 1;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->initialize();
    }

    /**
     * @return void
     */
    public function upgrade()
    {
        $newMode = $this->request->getBodyParams()[self::MODE_PARAM] ?? '';
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($this->scope->getScopeId()));

        $pluginMode->switch($newMode);
        $this->repository->savePluginMode($pluginMode, $this->scope->getScopeId());
    }
}
