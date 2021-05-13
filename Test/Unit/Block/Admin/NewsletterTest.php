<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Newsletter;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\ContactList;
use GrShareCode\ContactList\ContactListCollection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class NewsletterTest extends BaseTestCase
{
    /** @var Newsletter */
    private $newsletterBlock;
    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;
    /** @var Repository|MockObject */
    private $repository;

    public function setUp()
    {
        /** @var Context|MockObject $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore|MockObject $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        /** @var ApiClientFactory|MockObject $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);

        $apiClientFactory
            ->method('createGetResponseApiClient')
            ->willReturn($this->grApiClient);

        /** @var SerializerInterface $serializer */
        $serializer = $this->getMockWithoutConstructing(SerializerInterface::class);

        $this->newsletterBlock = new Newsletter(
            $context,
            $apiClientFactory,
            $this->repository,
            $magentoStore,
            $serializer
        );
    }

    /**
     * @test
     */
    public function shouldReturnLists()
    {
        $listId = '09809809';
        $name = 'listName';

        $expectedLists = new ContactListCollection();
        $expectedLists->add(new ContactList($listId, $name));

        $this->grApiClient
            ->expects(self::once())
            ->method('getContactList')
            ->willReturn([
                [
                    'campaignId' => $listId,
                    'name' => $name
                ]
            ]);

        $lists = $this->newsletterBlock->getLists();
        self::assertEquals($expectedLists, $lists);
    }

    /**
     * @test
     */
    public function shouldReturnNewsletterSettings()
    {
        $status = 1;
        $listId = '939438';
        $cycleDay = 3;
        $autoresponderId = '495898';

        $expectedSettings = new NewsletterSettings(
            $status,
            $listId,
            $cycleDay,
            $autoresponderId
        );

        $this->repository
            ->expects(self::once())
            ->method('getNewsletterSettings')
            ->willReturn([
                'status' => $status,
                'campaignId' => $listId,
                'cycleDay' => $cycleDay,
                'autoresponderId' => $autoresponderId
            ]);

        $settings = $this->newsletterBlock->getNewsletterSettings();
        self::assertEquals($expectedSettings, $settings);
    }
}
