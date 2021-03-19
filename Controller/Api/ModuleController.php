<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * @api
 */
class ModuleController
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $mode
     * @return mixed
     * @throws WebapiException
     */
    public function switch(string $mode)
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            $pluginMode->switch($mode);
            $this->repository->savePluginMode($pluginMode);
        } catch (PluginModeException $e) {
            throw new WebapiException(new Phrase($e->getMessage()));
        }
    }
}
