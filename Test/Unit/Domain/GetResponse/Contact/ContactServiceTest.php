<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Domain\GetResponse\Cart;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command\AddContact;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\ContactService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ContactServiceFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Contact\Command\AddContactCommand;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use GrShareCode\Contact\ContactService as GrContactService;
use PHPUnit\Framework\MockObject\MockObject;

class ContactServiceTest extends BaseTestCase
{
    /** @var ContactServiceFactory|MockObject */
    private $contactServiceFactoryMock;
    /** @var GrContactService|MockObject */
    private $grContactServiceMock;
    /** @var ContactService */
    private $contactService;
    /** @var Scope|MockObject */
    private $scope;

    protected function setUp()
    {
        $this->grContactServiceMock = $this->getMockWithoutConstructing(GrContactService::class);

        $this->contactServiceFactoryMock = $this->getMockWithoutConstructing(ContactServiceFactory::class);
        $this->contactServiceFactoryMock
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grContactServiceMock);

        $this->scope = $this->getMockWithoutConstructing(Scope::class);

        $this->contactService = new ContactService($this->contactServiceFactoryMock);
    }

    /**
     * @test
     * @dataProvider shouldCreateValidAddContactCommandProvider
     * @param AddContactCommand $expectedAddContactCommand
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $contactListId
     * @param null|int $dayOfCycle
     * @param ContactCustomFieldsCollection $customs
     */
    public function shouldCreateValidAddContactCommand(
        AddContactCommand $expectedAddContactCommand,
        $email,
        $firstName,
        $lastName,
        $contactListId,
        $dayOfCycle,
        ContactCustomFieldsCollection $customs
    ) {
        $this->contactServiceFactoryMock
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->grContactServiceMock);

        $this->grContactServiceMock
            ->expects(self::once())
            ->method('addContact')
            ->with($this->callback(function (AddContactCommand $addContactCommand) use ($expectedAddContactCommand) {
                return $addContactCommand == $expectedAddContactCommand
                    && $addContactCommand->getDayOfCycle() === $expectedAddContactCommand->getDayOfCycle();
            }));

        $this->contactService->addContact(
            new AddContact(
                $this->scope,
                $email,
                $firstName,
                $lastName,
                $contactListId,
                $dayOfCycle,
                $customs,
                true
            )
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
                    true
                ),
                'simple@example.com',
                'John',
                'Bravo',
                'D4K4',
                1,
                new ContactCustomFieldsCollection()
            ],
            // zabezpieczenie przed tym, aby w api day of cycle nie był ustawiony na '', jest po stronie shareCode'u
            [
                new AddContactCommand(
                    'simple@example.com',
                    'John Bravo',
                    'D4K4',
                    '',
                    new ContactCustomFieldsCollection(),
                    true
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
                    true
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
