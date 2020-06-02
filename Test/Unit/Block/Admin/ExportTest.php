<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Export;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\ContactList;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\Shop\Shop;
use GrShareCode\Shop\ShopsCollection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class ExportTest extends BaseTestCase
{
    /** @var Export */
    private $exportBlock;

    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;

    public function setUp()
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var Repository $repository */
        $repository = $this->getMockWithoutConstructing(Repository::class);
        /** @var ApiClientFactory $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        /** @var CustomFieldService $customFieldsService */
        $customFieldsService = $this->getMockWithoutConstructing(CustomFieldService::class);
        /** @var CustomFieldsMappingService $customFieldsMappingService */
        $customFieldsMappingService = $this->getMockWithoutConstructing(CustomFieldsMappingService::class);
        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        /** @var SerializerInterface $serializer */
        $serializer = $this->getMockWithoutConstructing(SerializerInterface::class);

        $apiClientFactory
            ->method('createGetResponseApiClient')
            ->willReturn($this->grApiClient);

        $this->exportBlock = new Export(
            $context,
            $apiClientFactory,
            $customFieldsService,
            $customFieldsMappingService,
            $magentoStore,
            $serializer,
            $repository
        );
    }

    /**
     * @test
     */
    public function shouldReturnShops()
    {
        $shopId = 'fk39c';
        $shopName = 'MagentoStore';

        $rawShops = [
            ['shopId' => $shopId, 'name' => $shopName]
        ];

        $expectedShops = new ShopsCollection();
        $expectedShops->add(new Shop($shopId, $shopName));

        $this->grApiClient->expects(self::once())
            ->method('getShops')
            ->willReturn($rawShops);

        $shops = $this->exportBlock->getShops();

        self::assertEquals($expectedShops, $shops);
    }

    /**
     * @test
     */
    public function shouldReturnLists()
    {
        $listId = 'fe930ru';
        $listName = 'MagentoList';

        $rawLists = [
            ['campaignId' => $listId, 'name' => $listName]
        ];

        $expectedLists = new ContactListCollection();
        $expectedLists->add(new ContactList($listId, $listName));

        $this->grApiClient->expects(self::once())
            ->method('getContactList')
            ->willReturn($rawLists);

        $shops = $this->exportBlock->getLists();

        self::assertEquals($expectedLists, $shops);
    }
}
