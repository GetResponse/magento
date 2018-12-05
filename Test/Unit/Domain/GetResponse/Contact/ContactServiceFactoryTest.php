<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\Contact\ContactServiceFactory as GrContactServiceFactory;

/**
 * Class ContactServiceFactoryTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact
 */
class ContactServiceFactoryTest extends BaseTestCase
{
    /** @var ShareCodeRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $shareCodeRepository;

    /** @var ApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $getResponseApiClientFactory;

    /** @var ContactServiceFactory */
    private $contactServiceFactory;

    /** @var GrContactServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $grContactServiceFactory;

    /**
     * @test
     */
    public function shouldCreateContactService()
    {
        $apiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $grContactService = $this->getMockWithoutConstructing(GrContactService::class);

        $this->getResponseApiClientFactory
            ->expects(self::once())
            ->method('createGetResponseApiClient')
            ->willReturn($apiClient);

        $this->grContactServiceFactory
            ->expects(self::once())
            ->method('create')
            ->with($apiClient, $this->shareCodeRepository, 'magento2')
            ->willReturn($grContactService);

        $result = $this->contactServiceFactory->create();
        $this->assertInstanceOf(GrContactService::class, $result);
    }

    protected function setUp()
    {
        $this->shareCodeRepository = $this->getMockWithoutConstructing(ShareCodeRepository::class);
        $this->getResponseApiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);
        $this->grContactServiceFactory = $this->getMockWithoutConstructing(GrContactServiceFactory::class);

        $this->contactServiceFactory = new ContactServiceFactory(
            $this->shareCodeRepository,
            $this->getResponseApiClientFactory,
            $this->grContactServiceFactory
        );
    }

}