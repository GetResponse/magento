<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\SubscribeFromNewsletter;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Newsletter\Model\Subscriber;
use PHPUnit\Framework\MockObject\MockObject;

class SubscribeFromNewsletterTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var ContactService|MockObject */
    private $contactServiceMock;
    /** @var SubscribeFromNewsletter */
    private $sut;

    public function setUp(): void
    {
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);
        /** @var ContactService|MockObject $contactServiceMock */
        $this->contactServiceMock = $this->getMockWithoutConstructing(ContactService::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);

        $this->sut = new SubscribeFromNewsletter(
            $this->repositoryMock,
            $this->contactServiceMock,
            $loggerMock
        );
    }

    /**
     * @test
     */
    public function shouldAddContactInOldPluginVersion(): void
    {
        $storeId = 3;
        $email = 'some@example.com';

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getEmail'])
            ->getMock();
        $subscriberMock->method('getStoreId')->willReturn($storeId);
        $subscriberMock->method('getEmail')->willReturn($email);
        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(EventObserver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscriber'])
            ->getMock();
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getNewsletterSettings')
            ->willReturn([
                'status' => 1,
                'campaignId' => 'xD3',
                'cycleDay' => 3,
                'autoresponderId' => '0'
            ]);

        $this->contactServiceMock
            ->expects(self::once())
            ->method('addContact');

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotAddContactInNewPluginVersion(): void
    {
        $storeId = 3;

        /** @var Subscriber|MockObject $subscriberMock */
        $subscriberMock = $this->getMockBuilder(Subscriber::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getEmail'])
            ->getMock();
        $subscriberMock->method('getStoreId')->willReturn($storeId);

        /** @var EventObserver|MockObject $observerMock */
        $observerMock = $this->getMockBuilder(EventObserver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSubscriber'])
            ->getMock();
        $observerMock->method('getSubscriber')->willReturn($subscriberMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::never())
            ->method('getNewsletterSettings');

        $this->contactServiceMock
            ->expects(self::never())
            ->method('addContact');

        $this->sut->execute($observerMock);
    }


}
