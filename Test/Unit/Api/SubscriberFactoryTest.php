<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Subscriber;
use GetResponse\GetResponseIntegration\Api\SubscriberFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use PHPUnit\Framework\MockObject\MockObject;

class SubscriberFactoryTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldCreateSubscriber(): void
    {
        $id = 1001;
        $email = 'some@email.com';
        $name = 'John Smith';
        $isMarketingAccepted = true;
        $storeId = 5;

        /** @var MagentoSubscriber|MockObject $magentoSubscriberMock */
        $magentoSubscriberMock = $this->getMockBuilder(MagentoSubscriber::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getEmail', 'getSubscriberFullName', 'isSubscribed'])
            ->addMethods(['getStoreId'])
            ->getMock();

        $magentoSubscriberMock->method('getId')->willReturnOnConsecutiveCalls($id);
        $magentoSubscriberMock->method('getEmail')->willReturnOnConsecutiveCalls($email);
        $magentoSubscriberMock->method('getSubscriberFullName')->willReturnOnConsecutiveCalls($name);
        $magentoSubscriberMock->method('isSubscribed')->willReturnOnConsecutiveCalls($isMarketingAccepted);
        $magentoSubscriberMock->method('getStoreId')->willReturnOnConsecutiveCalls($storeId);

        $expectedSubscriber = new Subscriber($id, $email, $name, $isMarketingAccepted, [], ['store_id' => $storeId]);

        $factory = new SubscriberFactory();
        $subscriber = $factory->create($magentoSubscriberMock);

        self::assertEquals($expectedSubscriber, $subscriber);
    }
}
