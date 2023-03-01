<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Controller;

use GetResponse\GetResponseIntegration\Controller\Api\ModuleControllerInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * @api
 */
class ModuleController extends ApiController implements ModuleControllerInterface
{
    /**
     * @param string $mode
     * @return void
     * @throws WebapiException
     */
    public function switch(string $mode): void
    {
        try {
            $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode());
            $pluginMode->switch($mode);

            if ($pluginMode->isNewVersion()) {
                foreach ($this->magentoStore->getMagentoStores() as $storeId => $storeName) {
                    $this->repository->clearDatabase($storeId);
                }
            }

            $this->repository->savePluginMode($pluginMode);
        } catch (PluginModeException $e) {
            throw new WebapiException(new Phrase($e->getMessage()));
        }
    }
}
