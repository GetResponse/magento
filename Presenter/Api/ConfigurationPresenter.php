<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api;

/**
 * @api
 */
class ConfigurationPresenter
{
    /**
     * @return string
     */
    public function getStatus(): string
    {
        return 'enabled';
    }

    /**
     * @return string
     */
    public function getPluginVersion(): string
    {
        return '25.0.4';
    }

    /**
     * @return FacebookPixelPresenter
     */
    public function getSections(): FacebookPixelPresenter
    {
        return new FacebookPixelPresenter();
    }
}
