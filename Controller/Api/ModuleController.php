<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * @api
 */
class ModuleController
{
    private $repository;
    private $magentoStore;

    public function __construct(Repository $repository, MagentoStore $magentoStore)
    {
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

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
            $this->repository->savePluginMode($pluginMode);

            if (false === $pluginMode->isNewVersion()) {
                return;
            }

            foreach ($this->magentoStore->getMagentoStores() as $storeId => $storeName) {
                $this->repository->clearConfiguration($storeId);
            }

        } catch (PluginModeException $e) {
            throw new WebapiException(new Phrase($e->getMessage()));
        }
    }
}
