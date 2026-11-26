<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Logger;

use Monolog\Logger as MonologLogger;

class Logger
{
    /** @var MonologLogger */
    private $logger;

    /**
     * @param MonologLogger $logger
     */
    public function __construct(MonologLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle add notice.
     *
     * @param mixed $message
     * @param array $context
     */
    public function addNotice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Handle add error.
     *
     * @param mixed $message
     * @param array $context
     */
    public function addError($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }
}
