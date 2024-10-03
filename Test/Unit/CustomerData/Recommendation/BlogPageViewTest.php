<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\CustomerData\Recommendation\BlogPageView;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Helper\CspNonceProviderFactory;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\Request\Http;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Cms\Block\Page as Subject;

class BlogPageViewTest extends BaseTestCase
{
    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var Http|MockObject */
    private $requestMock;
    /** @var Subject|MockObject */
    private $subjectMock;
    /** @var CspNonceProviderFactory|MockObject */
    private $cspNonceProviderFactoryMock;

    /** @var BlogPageView */
    private $sut;

    public function setUp(): void
    {
        $this->storeManagerMock = $this->getMockWithoutConstructing(StoreManagerInterface::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->requestMock = $this->getMockWithoutConstructing(Http::class);
        $this->subjectMock = $this->getMockWithoutConstructing(Subject::class, ['getNameInLayout'], ['___callParent']);
        $this->cspNonceProviderFactoryMock = $this->getMockWithoutConstructing(CspNonceProviderFactory::class);

        $this->sut = new BlogPageView(
            $this->storeManagerMock,
            $this->repositoryMock,
            $this->requestMock,
            $this->cspNonceProviderFactoryMock
        );
    }

    /**
     * @test
     */
    public function shouldReturnPayloadWithRecommendationPayload(): void
    {
        $storeId = 1;
        $html = "";

        $storeMock = $this->getMockWithoutConstructing(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock
            ->expects(self::once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_NEW);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getRecommendationSnippet')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'codeSnippet' => '']);

        $this->subjectMock
            ->expects(self::once())
            ->method('getNameInLayout')
            ->willReturn(BlogPageView::DISPLAY_BLOCK);

        $this->requestMock
            ->expects(self::once())
            ->method('getFullActionName')
            ->willReturn(BlogPageView::FULL_ACTION_NAME);

        $expectedPayload = [
            'pageType' => BlogPageView::PAGE_TYPE,
            'pageData' => []
        ];

        $expectedHtml = '<script type="text/javascript">const recommendationPayload = ' . json_encode($expectedPayload) . '</script>';

        $result = $this->sut->afterToHtml($this->subjectMock, $html);

        self::assertEquals($expectedHtml, $result);
    }

    /**
     * @test
     */
    public function shouldReturnPayloadWithoutRecommendationPayload(): void
    {
        $storeId = 1;
        $html = "";

        $storeMock = $this->getMockWithoutConstructing(StoreInterface::class);
        $storeMock->method('getId')->willReturn($storeId);

        $this->storeManagerMock
            ->expects(self::once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getPluginMode')
            ->willReturn(PluginMode::MODE_OLD);

        $this->repositoryMock
            ->expects(self::once())
            ->method('getRecommendationSnippet')
            ->with($storeId)
            ->willReturn(['isEnabled' => true, 'codeSnippet' => '']);

        $this->subjectMock
            ->expects(self::never())
            ->method('getNameInLayout')
            ->willReturn(BlogPageView::DISPLAY_BLOCK);

        $this->requestMock
            ->expects(self::never())
            ->method('getFullActionName')
            ->willReturn(BlogPageView::FULL_ACTION_NAME);

        $result = $this->sut->afterToHtml($this->subjectMock, $html);

        self::assertEquals($html, $result);
    }
}
