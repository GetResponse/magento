<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * @api
 */
interface ConfigurationControllerInterface
{
    /**
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;
     */
    public function list(): ConfigurationPresenter;

    /**
     * @return void
     */
    public function delete(): void;

    /**
     * @throws WebapiException
     * @return void
     * @param string $scope
     */
    public function update(string $scope): void;
}
