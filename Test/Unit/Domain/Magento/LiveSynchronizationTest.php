<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\Magento;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\RequestValidationException;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class LiveSynchronizationTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateLiveSynchronization()
    {
        $isActive = true;

        $liveSynchronization = new LiveSynchronization($isActive);

        self::assertEquals($isActive, $liveSynchronization->isActive());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRepository()
    {
        $liveSynchronization = LiveSynchronization::createFromRepository([]);

        self::assertEquals(false, $liveSynchronization->isActive());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRequest()
    {
        $isActive = true;

        $data = ['liveSynchronization' => ['isActive' => $isActive,]];

        $liveSynchronization = LiveSynchronization::createFromRequest($data);

        self::assertEquals($isActive, $liveSynchronization->isActive());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFacebookPixelFromRequest()
    {
        $this->expectException(RequestValidationException::class);
        LiveSynchronization::createFromRequest([]);
    }
}
