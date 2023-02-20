<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Observer;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GetResponse\GetResponseIntegration\Observer\CustomerRegisterSuccess;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use PHPUnit\Framework\MockObject\MockObject;

class CustomerRegisterSuccessTest extends BaseTestCase
{
    /** @var RequestInterface|MockObject */
    private $requestMock;
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var MagentoSubscriber|MockObject */
    private $magentoSubscriberMock;
    /** @var CustomerRegisterSuccess */
    private $sut;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockWithoutConstructing(RequestInterface::class);
        $this->magentoSubscriberMock = $this->getMockWithoutConstructing(MagentoSubscriber::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        /** @var Logger|MockObject $loggerMock */
        $loggerMock = $this->getMockWithoutConstructing(Logger::class);

        $this->sut = new CustomerRegisterSuccess(
            $this->requestMock,
            $this->magentoSubscriberMock,
            $this->repositoryMock,
            $loggerMock
        );
    }

    /**
     * @test
     */
    public function shouldSubscribeCustomer(): void
    {
        $storeId = 3;
        $customerId = 2456;

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(Customer::class, ['getId'], ['getStoreId']);

        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getId')->willReturn($customerId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomer']);
        $observerMock->method('getCustomer')->willReturn($customerMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'callbackUrl' => '', 'type' => LiveSynchronization::TYPE_ECOMMERCE]);

        $this->requestMock
            ->expects(self::once())
            ->method('getParam')
            ->willReturn(Subscriber::STATUS_SUBSCRIBED);

        $this->magentoSubscriberMock
            ->expects(self::once())
            ->method('subscribeCustomerById')
            ->with($customerId);

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotSubscribeCustomerWhenOldPluginVersion(): void
    {
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomer']);

        $this->requestMock
            ->expects(self::never())
            ->method('getParam');

        $this->magentoSubscriberMock
            ->expects(self::never())
            ->method('subscribeCustomerById');

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotSubscribeCustomerWhenLiveSynchronizationIsDisabled(): void
    {
        $storeId = 3;

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(Customer::class, ['getId'], ['getStoreId']);

        $customerMock->method('getStoreId')->willReturn($storeId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomer']);
        $observerMock->method('getCustomer')->willReturn($customerMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'callbackUrl' => '', 'type' => LiveSynchronization::TYPE_PRODUCT]);

        $this->requestMock
            ->expects(self::never())
            ->method('getParam');

        $this->magentoSubscriberMock
            ->expects(self::never())
            ->method('subscribeCustomerById');

        $this->sut->execute($observerMock);
    }

    /**
     * @test
     */
    public function shouldNotSubscribeCustomerWhenParamIsNotSend(): void
    {
        $storeId = 3;
        $customerId = 2456;

        /** @var Customer|MockObject $customerMock */
        $customerMock = $this->getMockWithoutConstructing(Customer::class, ['getId'], ['getStoreId']);

        $customerMock->method('getStoreId')->willReturn($storeId);
        $customerMock->method('getId')->willReturn($customerId);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMockWithoutConstructing(Observer::class, [], ['getCustomer']);
        $observerMock->method('getCustomer')->willReturn($customerMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getLiveSynchronization')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'callbackUrl' => '', 'type' => LiveSynchronization::TYPE_ECOMMERCE]);

        $this->requestMock
            ->expects(self::once())
            ->method('getParam')
            ->willReturn(Subscriber::STATUS_UNSUBSCRIBED);

        $this->magentoSubscriberMock
            ->expects(self::never())
            ->method('subscribeCustomerById');

        $this->sut->execute($observerMock);
    }
}
