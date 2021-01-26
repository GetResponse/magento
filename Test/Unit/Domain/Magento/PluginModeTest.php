<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class PluginModeTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreatePluginMode()
    {
        $mode = 'new';

        $sut = new PluginMode($mode);

        self::assertEquals($mode, $sut->getMode());
    }

    /**
     * @test
     */
    public function shouldCreatePluginModeFromRepository()
    {
        $mode = 'new';
        $sut = PluginMode::createFromRepository($mode);

        self::assertEquals($mode, $sut->getMode());
    }

    /**
     * @test
     */
    public function shouldSwitchPluginMode()
    {
        $oldMode = 'old';
        $newMode = 'new';

        $sut = new PluginMode($oldMode);
        $sut->switch($newMode);

        self::assertEquals($newMode, $sut->getMode());
    }

    /**
     * @test
     * @dataProvider switchModeProvider
     * @throws PluginModeException
     * @param string $newMode
     * @param string $oldMode
     */
    public function shouldThrowExceptionWhenSwitchingMode(string $oldMode, string $newMode)
    {
        $this->expectException(PluginModeException::class);

        $sut = new PluginMode($oldMode);
        $sut->switch($newMode);
    }

    public function switchModeProvider(): array
    {
        return [
            ['aaa', 'bbb'],
            ['', ''],
            ['old', 'old'],
            ['new', 'new'],
        ];
    }
}
