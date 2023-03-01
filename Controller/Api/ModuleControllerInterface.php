<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * @api
 */
interface ModuleControllerInterface
{
    /**
     * @param string $mode
     * @return void
     * @throws WebapiException
     */
    public function switch(string $mode): void;
}
