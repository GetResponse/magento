<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Registration;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\ContactList;
use GrShareCode\ContactList\ContactListCollection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class RegistrationTest extends BaseTestCase
{
    /** @var Registration registrationBlock */
    private $registrationBlock;
    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;
    /** @var Repository|MockObject */
    private $repository;

    public function setUp()
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        /** @var ApiClientFactory|MockObject $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        /** @var GetresponseApiClient grApiClient */
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

        $this->registrationBlock = new Registration(
            $context,
            $apiClientFactory,
            $this->repository,
            $customFieldsService,
            $customFieldsMappingService,
            $magentoStore,
            $serializer
        );
    }

    /**
     * @test
     */
    public function shouldReturnLists()
    {
        $id = '3949348';
        $name = 'contactList';

        $this->grApiClient
            ->expects(self::once())
            ->method('getContactList')
            ->willReturn([
                [
                    'campaignId' => $id,
                    'name' => $name
                ]
            ]);

        $expectedLists = new ContactListCollection();
        $expectedLists->add(new ContactList($id, $name));

        $lists = $this->registrationBlock->getLists();

        self::assertEquals($expectedLists, $lists);
    }

    /**
     * @test
     */
    public function shouldReturnRegistrationSettings()
    {
        $status = 1;
        $customFieldStatus = 1;
        $listId = '394938';
        $cycleDay = 4;
        $autoResponderId = '09898989';

        $expectedSettings = new SubscribeViaRegistration(
            $status,
            $customFieldStatus,
            $listId,
            $cycleDay,
            $autoResponderId
        );

        $this->repository
            ->expects(self::once())
            ->method('getRegistrationSettings')
            ->willReturn([
                'status' => $status,
                'customFieldsStatus' => $customFieldStatus,
                'campaignId' => $listId,
                'cycleDay' => $cycleDay,
                'autoresponderId' => $autoResponderId
            ]);

        $settings = $this->registrationBlock->getRegistrationSettings();

        self::assertEquals($expectedSettings, $settings);
    }
}
