<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

/**
 * @api
 */
interface SubscriberControllerInterface
{
    /**
     * @param int $pageSize
     * @param int $currentPage
     * @return mixed[]
     */
    public function list(int $pageSize, int $currentPage): array;
}
