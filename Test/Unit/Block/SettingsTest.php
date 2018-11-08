<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Block\Settings;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SettingsTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class SettingsTest extends BaseTestCase
{
    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|\PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var GetresponseApiClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $apiClientFactory;

    /** @var Settings */
    private $settingsBlock;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->apiClientFactory = $this->getMockWithoutConstructing(GetresponseApiClientFactory::class);

        $getresponseBlock = new Getresponse($this->repository, $this->apiClientFactory);
        $this->settingsBlock = new Settings(
            $this->context,
            $this->repository,
            $this->apiClientFactory,
            $getresponseBlock
        );
    }

    /**
     * @return array
     */
    public function shouldReturnAccountInfoProvider()
    {
        return [
            [[], new Account('', '', '', '', '', '', '', '', '', '', '')],
            [
                [
                    'accountId' => '33303939',
                    'firstName' => 'testName',
                    'lastName' => 'testLastName',
                    'email' => 'testEmail',
                    'companyName' => 'testCompanyName',
                    'phone' => 'testPhone',
                    'state' => 'testState',
                    'city' => 'testCity',
                    'street' => 'testStreet',
                    'zipCode' => 'testZipCode'
                ],
                new Account(
                    '33303939', 'testName', 'testLastName', 'testEmail', 'testCompanyName', 'testPhone', 'testState',
                    'testCity', 'testStreet', 'testZipCode', '')
            ]
        ];
    }
}
