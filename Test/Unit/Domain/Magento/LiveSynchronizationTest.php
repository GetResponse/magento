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
    public function shouldCreateLiveSynchronization(): void
    {
        $isActive = true;
        $callbackUrl = 'https://app.getrepsonse.com/#d5fj2dof3ij';

        $liveSynchronization = new LiveSynchronization($isActive, $callbackUrl, LiveSynchronization::TYPE_ECOMMERCE);

        self::assertEquals($isActive, $liveSynchronization->isActive());
        self::assertEquals($callbackUrl, $liveSynchronization->getCallbackUrl());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRepository(): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository([]);

        self::assertEquals(false, $liveSynchronization->isActive());
    }

    /**
     * @test
     */
    public function shouldCreateFacebookPixelFromRequest(): void
    {
        $isActive = true;
        $data = [
            'live_synchronization' => [
                'isEnabled' => $isActive,
                'url' => 'https://app.getrepsonse.com/#d5fj2dof3ij',
                'type' => LiveSynchronization::TYPE_ECOMMERCE
            ]
        ];

        $liveSynchronization = LiveSynchronization::createFromRequest($data);

        self::assertEquals($isActive, $liveSynchronization->isActive());
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenCreateFacebookPixelFromRequest(): void
    {
        $this->expectException(RequestValidationException::class);
        LiveSynchronization::createFromRequest([]);
    }
}
