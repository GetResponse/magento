<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Block\Admin;

use GetResponse\GetResponseIntegration\Block\Admin\Webform as WebformBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebformSettings;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\WebForm\WebForm;
use GrShareCode\WebForm\WebFormCollection;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;

class WebformTest extends BaseTestCase
{
    /** @var Repository|MockObject */
    private $repository;

    /** @var WebformBlock */
    private $webformBlock;

    /** @var GetresponseApiClient|MockObject */
    private $grApiClient;

    public function setUp()
    {
        /** @var Context $context */
        $context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        /** @var ApiClientFactory|MockObject $apiClientFactory */
        $apiClientFactory = $this->getMockWithoutConstructing(ApiClientFactory::class);

        /** @var MagentoStore $magentoStore */
        $magentoStore = $this->getMockWithoutConstructing(MagentoStore::class);

        $this->grApiClient = $this->getMockWithoutConstructing(GetresponseApiClient::class);

        $apiClientFactory
            ->method('createGetResponseApiClient')
            ->willReturn($this->grApiClient);

        $this->webformBlock = new WebformBlock(
            $context,
            $this->repository,
            $magentoStore,
            $apiClientFactory
        );
    }

    /**
     * @test
     */
    public function shouldReturnWebFormSettings()
    {
        $isEnabled = true;
        $url = 'http://getresponse.com/form.js';
        $webformId = '394934894';
        $sidebar = 'left';

        $this->repository
            ->expects(self::once())
            ->method('getWebformSettings')
            ->willReturn([
                'isEnabled' => $isEnabled,
                'url' => $url,
                'webformId' => $webformId,
                'sidebar' => $sidebar
            ]);

        $expectedSettings = new WebformSettings($isEnabled, $url, $webformId, $sidebar);
        $settings = $this->webformBlock->getWebFormSettings();

        self::assertEquals($expectedSettings, $settings);
    }

    /**
     * @test
     */
    public function shouldReturnWebforms()
    {
        $webFormId = '4989834';
        $name = 'WebFormX';
        $scriptUrl = 'https://getresponse.com/script.js';
        $listName = 'GrList';
        $status = 'enabled';
        $version = WebForm::VERSION_V1;

        $this->grApiClient
            ->expects(self::once())
            ->method('getWebForms')
            ->willReturn([
                [
                    'webformId' => $webFormId,
                    'name' => $name,
                    'scriptUrl' => $scriptUrl,
                    'campaign' => [
                        'name' => $listName
                    ],
                    'status' => $status
                ]
            ]);

        $this->grApiClient
            ->expects(self::once())
            ->method('getForms')
            ->willReturn([]);

        $expectedWebforms = new WebFormCollection();
        $expectedWebforms->add(new WebForm(
            $webFormId,
            $name,
            $scriptUrl,
            $listName,
            $status,
            $version
        ));
        $webforms = $this->webformBlock->getWebForms();

        self::assertEquals($expectedWebforms, $webforms);
    }
}
