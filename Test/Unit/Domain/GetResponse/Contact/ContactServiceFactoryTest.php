<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\Contact\ContactServiceFactory as GrContactServiceFactory;
use PHPUnit\Framework\MockObject\MockObject;

class ContactServiceFactoryTest extends BaseTestCase
{
    /** @var ShareCodeRepository|MockObject */
    private $shareCodeRepository;
    /** @var ApiClientFactory|MockObject */
    private $getResponseApiClientFactory;
    /** @var ContactServiceFactory */
    private $contactServiceFactory;
    /** @var GrContactServiceFactory|MockObject */
    private $grContactServiceFactory;
    /** @var Scope|MockObject */
    private $scope;

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

        $this->scope = $this->getMockWithoutConstructing(Scope::class);
    }

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

        $result = $this->contactServiceFactory->create($this->scope);
        self::assertInstanceOf(GrContactService::class, $result);
    }
}
