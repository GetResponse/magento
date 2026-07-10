<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

/**
 * @api
 */
interface SubscriberControllerInterface
{
    /**
     * Handle list.
     *
     * @param int $pageSize
     * @param int $currentPage
     * @return mixed[]
     */
    public function list(int $pageSize, int $currentPage): array;

    /**
     * Handle unsubscribe.
     *
     * @param string $scope
     * @param string $email
     * @return void
     */
    public function unsubscribe(string $scope, string $email): void;
}
