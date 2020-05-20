<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Webform;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Account\ReadModel\AccountReadModel;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class WebformTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;
    /** @var AccountReadModel|MockObject */
    private $accountReadModel;

    /** @var Webform() */
    private $webformBlock;

    public function setUp()
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);
        $this->accountReadModel = $this->getMockWithoutConstructing(AccountReadModel::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);

        $this->webformBlock = new Webform(
            $context,
            $magentoStore,
            $this->accountReadModel,
            $this->repository
        );
    }

    /**
     * @test
     * @dataProvider webFormToDisplayProvider
     * @param $expectedUrl
     * @param bool $isConnected
     * @param bool $isEnabled
     * @param string $sidebar
     * @param string $placement
     * @param string $url
     */
    public function shouldReturnWebFormUrlToDisplay(
        $expectedUrl,
        bool $isConnected,
        bool $isEnabled,
        string $sidebar,
        string $placement,
        string $url
    ) {
        $this->accountReadModel
            ->expects(self::once())
            ->method('isConnected')
            ->willReturn($isConnected);

        $this->repository
            ->method('getWebformSettings')
            ->willReturn([
                'isEnabled' => $isEnabled,
                'url' => $url,
                'webformId' => '39489383',
                'sidebar' => $sidebar
            ]);

        $webformUrl = $this->webformBlock->getWebFormUrlToDisplay($placement);
        self::assertEquals($expectedUrl, $webformUrl);
    }

    public function webFormToDisplayProvider(): array
    {
        return [
            [
               'expectedUrl' => null,
                'isConnected' => false,
                'isEnabled' => false,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => null,
                'isConnected' => true,
                'isEnabled' => false,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => null,
                'isConnected' => true,
                'isEnabled' => true,
                'sidebar' => 'bottom',
                'placement' => 'top',
                'url' => 'http://getresponse.com/script.js'
            ],
            [
                'expectedUrl' => 'http://getresponse.com/script.js',
                'isConnected' => true,
                'isEnabled' => true,
                'sidebar' => 'bottom',
                'placement' => 'bottom',
                'url' => 'http://getresponse.com/script.js'
            ],
        ];
    }
}
