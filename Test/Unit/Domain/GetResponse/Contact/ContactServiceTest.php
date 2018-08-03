<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\ContactService as GrContactService;

/**
 * Class ContactServiceTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart
 */
class ContactServiceTest extends BaseTestCase
{
    /** @var ContactServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $contactServiceFactory;

    /** @var GrContactService|\PHPUnit_Framework_MockObject_MockObject */
    private $grContactService;

    /** @var ContactService */
    private $sut;

    /**
     * @test
     */
    public function shouldGetContactByEmailAndContactListId()
    {
        $email = 'kowalski@getresponse.com';
        $contactListId = 'As34d';

        $this->grContactService
            ->expects(self::once())
            ->method('getContactByEmail')
            ->with($email, $contactListId);

        $this->sut->getContactByEmail($email, $contactListId);
    }

    protected function setUp()
    {
        $this->grContactService = $this->getMockWithoutConstructing(GrContactService::class);

        $this->contactServiceFactory = $this->getMockWithoutConstructing(ContactServiceFactory::class);
        $this->contactServiceFactory
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grContactService);

        $this->sut = new ContactService($this->contactServiceFactory);

    }


}