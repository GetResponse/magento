<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

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

    public function switch(string $newMode)
    {
        $this->validate($newMode);
        $this->mode = $newMode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    private function validate(string $newMode)
    {
        if (!in_array($newMode, [self::MODE_OLD, self::MODE_NEW])) {
            throw new RuntimeException('Incorrect mode');
        }

        if ($this->mode === self::MODE_NEW) {
            throw new RuntimeException('Cannot change mode when plugin is in new mode');
        }

        if ($this->mode === self::MODE_OLD && $newMode === self::MODE_OLD) {
            throw new RuntimeException('Modes are the same.');
        }
    }

    public function isNewVersion(): bool
    {
        return $this->mode === self::MODE_NEW;
    }
}
