<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\GetresponseApiClient;

/**
 * Class ContactServiceFactoryTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact
 */
class ContactServiceFactoryTest extends BaseTestCase
{
    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

    /** @var ShareCodeRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $shareCodeRepository;

    /** @var ContactServiceFactory */
    private $contactServiceFactory;

    /**
     * @test
     */
    public function shouldCreateContactService()
    {
        $this->magentoRepository
            ->expects(self::once())
            ->method('getConnectionSettings')
            ->willReturn([
                'url' => '',
                'domain' => 'https://my_private_custom_page_url',
                'apiKey' => 'GetResponseApiKey'
            ]);

        $this->magentoRepository
            ->expects(self::once())
            ->method('getGetResponsePluginVersion')
            ->willReturn('20.1.1');

        $result = $this->contactServiceFactory->create();

        $this->assertInstanceOf(GrContactService::class, $result);

        $this->assertInstanceOf(
            GetresponseApiClient::class,
            $this->getObjectAttribute($result, 'getresponseApiClient')
        );

    }

    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->shareCodeRepository = $this->getMockWithoutConstructing(ShareCodeRepository::class);
        $this->contactServiceFactory = new ContactServiceFactory($this->magentoRepository, $this->shareCodeRepository);
    }


}