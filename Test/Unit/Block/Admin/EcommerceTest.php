<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Ecommerce;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Ecommerce\ReadModel\EcommerceReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\ContactList;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\Shop\Shop;
use GrShareCode\Shop\ShopsCollection;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class EcommerceTest extends BaseTestCase
{
    /** @var EcommerceReadModel|MockObject */
    private $ecommerceReadModel;

    /** @var Ecommerce ecommerceBlock */
    private $ecommerceBlock;

    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;

    public function setUp()
    {
        /** @var Context|MockObject $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore|MockObject $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        /** @var ApiClientFactory|MockObject $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->ecommerceReadModel = $this->getMockWithoutConstructing(EcommerceReadModel::class);

        $apiClientFactory
            ->method('createGetResponseApiClient')
            ->willReturn($this->grApiClient);

        $this->ecommerceBlock = new Ecommerce(
            $context,
            $apiClientFactory,
            $magentoStore,
            $this->ecommerceReadModel
        );
    }

    /**
     * @test
     */
    public function shouldReturnShopStatus()
    {
        $expectedShopStatus = 'enabled';

        $this->ecommerceReadModel
            ->expects(self::once())
            ->method('getShopStatus')
            ->willReturn($expectedShopStatus);

        $status = $this->ecommerceBlock->getShopStatus();
        self::assertEquals($expectedShopStatus, $status);
    }

    /**
     * @test
     */
    public function shouldReturnShopId()
    {
        $expectedShopId = '330330';

        $this->ecommerceReadModel
            ->expects(self::once())
            ->method('getShopId')
            ->willReturn($expectedShopId);

        $shopId = $this->ecommerceBlock->getCurrentShopId();
        self::assertEquals($expectedShopId, $shopId);
    }

    /**
     * @test
     */
    public function shouldReturnEcommerceListId()
    {
        $expectedListId = '330330';

        $this->ecommerceReadModel
            ->expects(self::once())
            ->method('getListId')
            ->willReturn($expectedListId);

        $listId = $this->ecommerceBlock->getEcommerceListId();
        self::assertEquals($expectedListId, $listId);
    }

    /**
     * @test
     */
    public function shouldReturnShops()
    {
        $shopId = '489348934';
        $name = 'shopName';

        $expectedShops = new ShopsCollection();
        $expectedShops->add(new Shop($shopId, $name));

        $this->grApiClient
            ->expects(self::once())
            ->method('getShops')
            ->willReturn([
                [
                    'shopId' => $shopId,
                    'name' => $name
                ]
            ]);

        $shops = $this->ecommerceBlock->getShops();
        self::assertEquals($expectedShops, $shops);
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

        $lists = $this->ecommerceBlock->getLists();
        self::assertEquals($expectedLists, $lists);
    }
}
