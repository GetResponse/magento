<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Logger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * Handle add notice.
     *
     * @param mixed $message
     * @param array $context
     */
    public function addNotice($message, array $context = [])
    {
        if (method_exists(MonologLogger::class, 'notice')) {
            parent::notice($message, $context);
        } elseif (method_exists(MonologLogger::class, 'addNotice')) {
            parent::addNotice($message, $context);
        }
    }

    /**
     * Handle add error.
     *
     * @param mixed $message
     * @param array $context
     */
    public function addError($message, array $context = [])
    {
        if (method_exists(MonologLogger::class, 'error')) {
            parent::error($message, $context);
        } elseif (method_exists(MonologLogger::class, 'addError')) {
            parent::addError($message, $context);
        }
    }
}
