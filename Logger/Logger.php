<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Logger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * We need to maintain below methods due to Magento changed Monolog version from 1.x where addNotice and addError
     * methods exists to version 2.x where notice and error methods are available right now.
     * To perform full support for our clients using Magento 2.x we decide to modify our logger.
     */
    public function addNotice($message, array $context = [])
    {
        if (method_exists(MonologLogger::class, 'notice')) {
            parent::notice($message, $context);
        } elseif(method_exists(MonologLogger::class, 'addNotice')) {
            parent::addNotice($message, $context);
        }
    }

    public function addError($message, array $context = [])
    {
        if (method_exists(MonologLogger::class, 'error')) {
            parent::error($message, $context);
        } elseif(method_exists(MonologLogger::class, 'addError')) {
            parent::addError($message, $context);
        }
    }
}
