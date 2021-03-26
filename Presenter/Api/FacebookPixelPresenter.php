<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api;

/**
 * @api
 */
class FacebookPixelPresenter
{
    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'code';
    }
}
