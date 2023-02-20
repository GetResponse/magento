<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Lists;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\ContactList\FromFields;
use GrShareCode\ContactList\FromFieldsCollection;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class ListsTest extends BaseTestCase
{
    /** @var Lists */
    private $listsBlock;

    /** @var StoreReadModel|MockObject */
    private $storeReadModel;

    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;

    protected function setUp(): void
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);

        /** @var ApiClientFactory|MockObject $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);

        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        $this->storeReadModel = $this->getMockWithoutConstructing(StoreReadModel::class);

        $apiClientFactory
            ->method('createGetResponseApiClient')
            ->willReturn($this->grApiClient);

        $this->listsBlock = new Lists(
            $context,
            $apiClientFactory,
            $magentoStore,
            $this->storeReadModel
        );
    }

    /**
     * @test
     */
    public function shouldReturnAccountFromFields()
    {
        $fromFieldId = '3938984';
        $name = 'firldName';
        $email = 'field@example.com';

        $rawFromFields = [
            [
                'fromFieldId' => $fromFieldId,
                'name' => $name,
                'email' => $email
            ]
        ];

        $expectedFields = new FromFieldsCollection();
        $expectedFields->add(new FromFields($fromFieldId, $name, $email));

        $this->grApiClient
            ->expects(self::once())
            ->method('getFromFields')
            ->willReturn($rawFromFields);

        $fields = $this->listsBlock->getAccountFromFields();

        self::assertEquals($expectedFields, $fields);
    }

    /**
     * @test
     */
    public function shouldReturnSubscriptionConfirmationSubject()
    {
        $expectedSubscription = ['test398493489'];

        $this->storeReadModel
            ->expects(self::once())
            ->method('getStoreLanguage')
            ->willReturn('PL');

        $this->grApiClient
            ->expects(self::once())
            ->method('getSubscriptionConfirmationSubject')
            ->willReturn($expectedSubscription);

        $subject = $this->listsBlock->getSubscriptionConfirmationsSubject();
        self::assertEquals($expectedSubscription, $subject);
    }

    /**
     * @test
     */
    public function shouldReturnSubscriptionConfirmationBody()
    {
        $expectedSubscription = ['test398493489'];

        $this->storeReadModel
            ->expects(self::once())
            ->method('getStoreLanguage')
            ->willReturn('PL');

        $this->grApiClient
            ->expects(self::once())
            ->method('getSubscriptionConfirmationBody')
            ->willReturn($expectedSubscription);

        $subject = $this->listsBlock->getSubscriptionConfirmationsBody();
        self::assertEquals($expectedSubscription, $subject);
    }

    /**
     * @test
     * @dataProvider backUrlProvider
     * @param $backUrl
     * @param $expectedUrl
     */
    public function shouldReturnBackUrl($backUrl, $expectedUrl)
    {
        $url = $this->listsBlock->getBackUrl($backUrl);

        self::assertEquals($expectedUrl, $url);
    }

    public static function backUrlProvider(): array
    {
        return [
            ['export', 'getresponse/export/index'],
            ['registration', 'getresponse/registration/index'],
            ['newsletter', 'getresponse/newsletter/index']
        ];
    }
}
