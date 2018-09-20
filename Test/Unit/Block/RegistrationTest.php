<?php

namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Block\Registration as RegistrationBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;


/**
 * Class RegistrationTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class RegistrationTest extends BaseTestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var RegistrationBlock registrationBlock */
    private $registrationBlock;

    /** @var GetresponseApiClient|PHPUnit_Framework_MockObject_MockObject */
    private $grApiClient;

    /** @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->repositoryFactory->method('createGetResponseApiClient')->willReturn($this->grApiClient);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->registrationBlock = new RegistrationBlock($this->context, $this->repository, $this->repositoryFactory,
            $getresponseBlock);
    }

    /**
     * @test
     *
     * @param array $rawSettings
     * @param ConnectionSettings $expectedSettings
     *
     * @dataProvider shouldReturnConnectionSettingsProvider
     */
    public function shouldReturnConnectionSettings(array $rawSettings, ConnectionSettings $expectedSettings)
    {
        $this->repository->expects($this->once())->method('getConnectionSettings')->willReturn($rawSettings);
        $settings = $this->registrationBlock->getConnectionSettings();

        self::assertEquals($expectedSettings, $settings);
    }

    /**
     * @return array
     */
    public function shouldReturnConnectionSettingsProvider()
    {
        return [
            [[], new ConnectionSettings('', '', '')],
            [
                [
                    'apiKey' => 'testApiKey',
                    'url' => 'testUrl',
                    'domain' => 'testDomain'
                ],
                new ConnectionSettings('testApiKey', 'testUrl', 'testDomain')
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldReturnAutoResponders()
    {
        $campaignId = 'x3v';
        $name = 'testName';
        $subject = 'testSubject';
        $dayOfCycle = 5;
        $responderId = 'q31';

        $triggerSettings = [
            'selectedCampaigns' => [$campaignId],
            'dayOfCycle' => $dayOfCycle
        ];

        $rawAutoResponder = [
            'autoresponderId' => $responderId,
            'campaignId' => $campaignId,
            'status' => 'enabled',
            'triggerSettings' => $triggerSettings,
            'name' => $name,
            'subject' => $subject
        ];

        $rawAutoResponders = [
            $responderId => $rawAutoResponder
        ];

        $this->grApiClient->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoResponders);

        $autoResponders = $this->registrationBlock->getAutoResponders();

        self::assertTrue(is_array($autoResponders));

        if (count($autoResponders) > 0) {
            self::assertEquals($name, $autoResponders[$campaignId][$responderId]['name']);
            self::assertEquals($subject, $autoResponders[$campaignId][$responderId]['subject']);
        }
    }

    /**
     * @test
     */
    public function shouldReturnAutoRespondersForFrontend()
    {
        $campaignId = 'x3v';
        $name = 'testName';
        $subject = 'testSubject';
        $dayOfCycle = 5;
        $responderId = 'q31';

        $triggerSettings = [
            'selectedCampaigns' => [$campaignId],
            'dayOfCycle' => $dayOfCycle
        ];

        $rawAutoResponder = [
            'autoresponderId' => $responderId,
            'campaignId' => $campaignId,
            'status' => 'enabled',
            'triggerSettings' => $triggerSettings,
            'name' => $name,
            'subject' => $subject
        ];

        $rawAutoResponders = [
            $responderId => $rawAutoResponder
        ];

        $this->grApiClient->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoResponders);

        $autoResponders = $this->registrationBlock->getAutoRespondersForFrontend();

        self::assertTrue(is_array($autoResponders));

        if (count($autoResponders) > 0) {
            self::assertEquals($name, $autoResponders[$campaignId][$responderId]['name']);
            self::assertEquals($subject, $autoResponders[$campaignId][$responderId]['subject']);
            self::assertEquals($dayOfCycle, $autoResponders[$campaignId][$responderId]['dayOfCycle']);
        }
    }

    /**
     * @test
     * @param array $settings
     * @param RegistrationSettings $expectedExportSettings
     *
     * @dataProvider shouldReturnRegistrationsSettingsProvider
     */
    public function shouldReturnRegistrationsSettings(array $settings, RegistrationSettings $expectedExportSettings)
    {
        $this->repository->expects($this->once())->method('getRegistrationSettings')->willReturn($settings);
        $exportSettings = $this->registrationBlock->getRegistrationSettings();

        self::assertEquals($exportSettings, $expectedExportSettings);
    }

    /**
     * @return array
     */
    public function shouldReturnRegistrationsSettingsProvider()
    {
        return [
            [[], new RegistrationSettings(0, 0, '', 0, '')],
            [
                [
                    'status' => 1,
                    'customFieldsStatus' => 0,
                    'campaignId' => '1v4',
                    'cycleDay' => 6,
                    'autoresponderId' => 'xyZ'
                ],
                new RegistrationSettings(1, 0, '1v4', 6, 'xyZ')
            ]
        ];
    }

    /**
     * @test
     *
     * @param array $rawCustoms
     * @param CustomField $expectedFirstCustom
     * @dataProvider shouldReturnCustomsProvider
     */
    public function shouldReturnCustoms(array $rawCustoms, CustomField $expectedFirstCustom)
    {
        $this->repository->expects($this->once())->method('getCustoms')->willReturn($rawCustoms);

        $customs = $this->registrationBlock->getCustoms();
        self::assertInstanceOf(CustomFieldsCollection::class, $customs);
        if (count($customs->getCustoms()) > 0) {

            $custom = $customs->getCustoms()[0];
            self::assertInstanceOf(CustomField::class, $custom);
            self::assertEquals($expectedFirstCustom->getId(), $custom->getId());
            self::assertEquals($expectedFirstCustom->getCustomField(), $custom->getCustomField());
            self::assertEquals($expectedFirstCustom->getCustomField(), $custom->getCustomField());
            self::assertEquals($expectedFirstCustom->getCustomValue(), $custom->getCustomValue());
            self::assertEquals($expectedFirstCustom->getCustomName(), $custom->getCustomName());
            self::assertEquals($expectedFirstCustom->isDefault(), $custom->isDefault());
            self::assertEquals($expectedFirstCustom->isActive(), $custom->isActive());
        }
    }

    /**
     * @return array
     */
    public function shouldReturnCustomsProvider()
    {
        $id = 3;
        $customField = 'testCustomField';
        $customValue = 'testCustomValue';
        $customName = 'testCustomName';
        $isDefault = 1;
        $isActive = 0;

        $rawCustomField = new \stdClass();
        $rawCustomField->id = $id;
        $rawCustomField->customField = $customField;
        $rawCustomField->customValue = $customValue;
        $rawCustomField->customName = $customName;
        $rawCustomField->isDefault = $isDefault;
        $rawCustomField->isActive = $isActive;

        $customField = new CustomField($id, $customField, $customValue, $customName, $isDefault, $isActive);

        return [
            [[], new CustomField(0, '', '', '', 0, 0)],
            [
                [$rawCustomField],
                $customField
            ]
        ];
    }
}
