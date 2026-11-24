<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter;

interface ConfigurationControllerInterface
{
    /**
     * Handle list.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\ConfigurationPresenter
     */
    public function list(): ConfigurationPresenter;

    /**
     * Handle delete.
     *
     * @return void
     */
    public function delete(): void;

    /**
     * Handle update.
     *
     * @param string $scope
     * @return void
     */
    public function update(string $scope): void;

    /**
     * Update only product availability settings for a store.
     *
     * @param int $scope
     * @return void
     */
    public function updateProductAvailability(int $scope): void;
}
