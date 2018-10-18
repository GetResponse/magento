<?php

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\AddContactCommand;
use GrShareCode\Contact\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService as GrContactService;

/**
 * Class ContactServiceTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart
 */
class ContactServiceTest extends BaseTestCase
{
    /** @var ContactServiceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $contactServiceFactoryMock;

    /** @var GrContactService|\PHPUnit_Framework_MockObject_MockObject */
    private $grContactServiceMock;

    /** @var ContactService */
    private $contactService;

    protected function setUp()
    {
        $this->grContactServiceMock = $this->getMockWithoutConstructing(GrContactService::class);

        $this->contactServiceFactoryMock = $this->getMockWithoutConstructing(ContactServiceFactory::class);
        $this->contactServiceFactoryMock
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grContactServiceMock);

        $this->contactService = new ContactService($this->contactServiceFactoryMock);

    }

    /**
     * @test
     */
    public function shouldGetContactByEmailAndContactListId()
    {
        $email = 'kowalski@getresponse.com';
        $contactListId = 'As34d';

        $this->grContactServiceMock
            ->expects(self::once())
            ->method('getContactByEmail')
            ->with($email, $contactListId);

        $this->contactService->getContactByEmail($email, $contactListId);
    }

    /**
     * @test
     * @dataProvider shouldCreateValidAddContactCommandProvider
     * @param AddContactCommand $expectedAddContactCommand
     * @param $email
     * @param $firstName
     * @param $lastName
     * @param $campaignId
     * @param $dayOfCycle
     * @param $customs
     */
    public function shouldCreateValidAddContactCommand(
        AddContactCommand $expectedAddContactCommand,
        $email,
        $firstName,
        $lastName,
        $campaignId,
        $dayOfCycle,
        $customs
    ) {
        $this->contactServiceFactoryMock
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grContactServiceMock);

        $this->grContactServiceMock
            ->expects(self::once())
            ->method('createContact')
            ->with($expectedAddContactCommand);

        $this->contactService->createContact(
            $email,
            $firstName,
            $lastName,
            $campaignId,
            $dayOfCycle,
            $customs
        );
    }

    /**
     * @return array
     */
    public function shouldCreateValidAddContactCommandProvider()
    {
        return [
            [
                new AddContactCommand(
                    'simple@example.com',
                    'John Bravo',
                    'D4K4',
                    1,
                    new ContactCustomFieldsCollection(),
                    Config::ORIGIN_NAME
                ),
                'simple@example.com',
                'John',
                'Bravo',
                'D4K4',
                1,
                new ContactCustomFieldsCollection()
            ],
            // zabezpieczenie przed tym, aby w api day of cycle nie był ustawiony na ''
            [
                new AddContactCommand(
                    'simple@example.com',
                    'John Bravo',
                    'D4K4',
                    NULL,
                    new ContactCustomFieldsCollection(),
                    Config::ORIGIN_NAME
                ),
                'simple@example.com',
                'John',
                'Bravo',
                'D4K4',
                '',
                new ContactCustomFieldsCollection()
            ],
            // empty name
            [
                new AddContactCommand(
                    'simple@example.com',
                    '',
                    'D4K4',
                    1,
                    new ContactCustomFieldsCollection(),
                    Config::ORIGIN_NAME
                ),
                'simple@example.com',
                '',
                '',
                'D4K4',
                1,
                new ContactCustomFieldsCollection()
            ]
        ];
    }

}