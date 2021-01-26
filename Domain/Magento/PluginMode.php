<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class PluginMode
{
    const MODE_OLD = 'old';
    const MODE_NEW = 'new';

    private $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public static function createFromRepository($pluginMode): self
    {
        return new self($pluginMode ?? self::MODE_OLD);
    }

    /**
     * @throws PluginModeException
     * @param string $newMode
     */
    public function switch(string $newMode)
    {
        $this->validate($newMode);
        $this->mode = $newMode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @throws PluginModeException
     * @param string $newMode
     */
    private function validate(string $newMode)
    {
        if (!in_array($newMode, [self::MODE_OLD, self::MODE_NEW])) {
            throw PluginModeException::createForInvalidPluginMode('Incorrect mode');
        }

        if ($this->mode === $newMode) {
            throw PluginModeException::createForInvalidPluginMode('Modes are the same.');
        }
    }

    public function isNewVersion(): bool
    {
        return $this->mode === self::MODE_NEW;
    }
}
