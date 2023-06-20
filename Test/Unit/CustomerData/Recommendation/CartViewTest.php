<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\CustomerData\Recommendation;

use GetResponse\GetResponseIntegration\CustomerData\Recommendation\CartView;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\App\Request\Http;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Checkout\Block\Cart as Subject;

class CartViewTest extends BaseTestCase
{
    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;
    /** @var Repository|MockObject */
    private $repositoryMock;
    /** @var Http|MockObject */
    private $requestMock;
    /** @var Subject|MockObject */
    private $subjectMock;

    /** @var CartView */
    private $sut;

    public function setUp(): void
    {
        $this->storeManagerMock = $this->getMockWithoutConstructing(StoreManagerInterface::class);
        $this->repositoryMock = $this->getMockWithoutConstructing(Repository::class);
        $this->requestMock = $this->getMockWithoutConstructing(Http::class);
        $this->subjectMock = $this->getMockWithoutConstructing(Subject::class, ['getNameInLayout'], ['___callParent']);

        $this->sut = new CartView(
            $this->storeManagerMock,
            $this->repositoryMock,
            $this->requestMock
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
            ->willReturn(CartView::DISPLAY_BLOCK);

        $this->requestMock
            ->expects(self::once())
            ->method('getFullActionName')
            ->willReturn(CartView::FULL_ACTION_NAME);

        $expectedPayload = [
            'pageType' => CartView::PAGE_TYPE,
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
            ->willReturn(CartView::DISPLAY_BLOCK);

        $this->requestMock
            ->expects(self::never())
            ->method('getFullActionName')
            ->willReturn(CartView::FULL_ACTION_NAME);

        $result = $this->sut->afterToHtml($this->subjectMock, $html);

        self::assertEquals($html, $result);
    }
}
