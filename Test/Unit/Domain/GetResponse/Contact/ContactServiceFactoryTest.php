<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\GetresponseApi;

/**
 * Class ContactServiceFactoryTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Contact
 */
class ContactServiceFactoryTest extends BaseTestCase
{
    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoRepository;

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
                'domain' => 'https://api3.getresponse360.pl/v3',
                'url' => 'https://my_private_custom_page_url',
                'apiKey' => 'GetResponseApiKey'
            ]);

        $this->magentoRepository
            ->expects(self::once())
            ->method('getGetResponsePluginVersion')
            ->willReturn('20.1.1');

        $result = $this->contactServiceFactory->create();

        $this->assertInstanceOf(GrContactService::class, $result);

        $this->assertInstanceOf(
            GetresponseApi::class,
            $this->getObjectAttribute($result, 'getresponseApi')
        );

    }

    protected function setUp()
    {
        $this->magentoRepository = $this->getMockWithoutConstructing(Repository::class);
        $this->contactServiceFactory = new ContactServiceFactory($this->magentoRepository);
    }


}