<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderAddressInterface;
use \Magento\Sales\Model\Order as MagentoOrder;

class ApiService
{
    private $repository;
    private $cartHelper;
    private $httpClient;
    private $categoryRepository;

    public function __construct(
        Repository $repository,
        CartHelper $cartHelper,
        HttpClient $httpClient,
        CategoryRepository $categoryRepository
    ) {
        $this->repository = $repository;
        $this->cartHelper = $cartHelper;
        $this->httpClient = $httpClient;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @throws HttpClientException
     */
    public function createCart(Quote $quote, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $cartDTO = new Cart(
            (int)$quote->getId(),
            $this->createCustomerFromQuote($quote),
            $this->createLinesFromQuote($quote),
            (float)$quote->getGrandTotal(),
            (float)$quote->getGrandTotal(),
            $quote->getQuoteCurrencyCode(),
            $this->cartHelper->getCartUrl(),
            $quote->getCreatedAt(),
            $quote->getUpdatedAt()
        );

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $cartDTO->toApiRequest()
        );
    }

    /**
     * @throws HttpClientException
     */
    public function createOrder(MagentoOrder $order, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $orderDTO = new Order(
            (int)$order->getId(),
            (int)$order->getQuoteId(),
            $order->getCustomerEmail(),
            $this->createCustomerFromOrder($order),
            $this->createLinesFromOrder($order),
            null,
            (float)$order->getBaseSubtotal(),
            (float)$order->getGrandTotal(),
            (float)$order->getBaseShippingAmount(),
            $order->getOrderCurrencyCode(),
            $order->getStatus(),
            null,
            $this->getAddressFromOrder($order->getShippingAddress()),
            $this->getAddressFromOrder($order->getBillingAddress()),
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $orderDTO->toApiRequest('order/create')
        );
    }

    /**
     * @throws HttpClientException
     */
    public function updateOrder(MagentoOrder $order, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $orderDTO = new Order(
            (int)$order->getId(),
            (int)$order->getQuoteId(),
            $order->getCustomerEmail(),
            $this->createCustomerFromOrder($order),
            $this->createLinesFromOrder($order),
            null,
            (float)$order->getBaseSubtotal(),
            (float)$order->getGrandTotal(),
            (float)$order->getBaseShippingAmount(),
            $order->getOrderCurrencyCode(),
            $order->getStatus(),
            null,
            $this->getAddressFromOrder($order->getShippingAddress()),
            $this->getAddressFromOrder($order->getBillingAddress()),
            $order->getCreatedAt(),
            $order->getUpdatedAt()
        );

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $orderDTO->toApiRequest('order/update')
        );
    }

    private function createLinesFromQuote(Quote $quote): array
    {
        $lines = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $children = $item->getChildren();

            if (!empty($children)) {
                /** @var Item $child */
                foreach ($children as $child) {
                    $lines[] = new Line(
                        (int)$child->getProduct()->getId(),
                        (float)$child->getPrice(),
                        (float)$child->getPriceInclTax(),
                        (int)$child->getQty()
                    );
                }
            } else {
                $lines[] = new Line(
                    (int)$item->getProduct()->getId(),
                    (float)$item->getPrice(),
                    (float)$item->getPriceInclTax(),
                    (int)$item->getQty()
                );
            }
        }

        return $lines;
    }

    private function createCustomerFromQuote(Quote $quote): Customer
    {
        $customer = $quote->getCustomer();

        return new Customer(
            (int)$customer->getId(),
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            true,
            [],
            []
        );
    }

    private function createCustomerFromOrder(MagentoOrder $order): Customer
    {
        return new Customer(
            (int)$order->getCustomerId(),
            $order->getCustomerEmail(),
            $order->getCustomerFirstname(),
            $order->getCustomerLastname(),
            true,
            [],
            []
        );
    }

    private function createLinesFromOrder(MagentoOrder $order): array
    {
        $lines = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $children = $item->getChildren();

            if (!empty($children)) {
                /** @var Item $child */
                foreach ($children as $child) {

                    $lines[] = new Line(
                        (int)$child->getProduct()->getId(),
                        (float)$child->getPrice(),
                        (float)$child->getPriceInclTax(),
                        (int)$child->getQtyOrdered()
                    );
                }
            } else {

                $lines[] = new Line(
                    (int)$item->getProduct()->getId(),
                    (float)$item->getPrice(),
                    (float)$item->getPriceInclTax(),
                    (int)$item->getQtyOrdered()
                );
            }
        }

        return $lines;
    }

    private function getAddressFromOrder(?OrderAddressInterface $address): Address
    {
        $address1 = $address->getStreet()[0] ?? '';
        $address2 = $address->getStreet()[1] ?? '';

        return new Address(
            sprintf('%s %s', $address->getFirstname(), $address->getLastname()),
            $address->getCountryId(),
            $address->getFirstname(),
            $address->getLastname(),
            $address1,
            $address2,
            $address->getCity(),
            $address->getPostcode(),
            $address->getRegion(),
            '',
            $address->getTelephone(),
            $address->getCompany()
        );
    }

    public function createProduct(MagentoProduct $product, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $variants = [];

        if ($product->getTypeId() === Configurable::TYPE_CODE) {

            $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
            /** @var MagentoProduct $childProduct */
            foreach ($usedProducts as $childProduct) {
                $images = [];
                foreach ($childProduct->getMediaGalleryImages() as $image) {
                    $images[] = new Image(
                        $image->getData('url'),
                        (int)$image->getData('position')
                    );
                }

                $variants[] = new Variant(
                    (int)$childProduct->getId(),
                    $childProduct->getName(),
                    $childProduct->getSku(),
                    (float)$childProduct->getPrice(),
                    (float)$childProduct->getPrice(),
                    null,
                    null,
                    0,
                    0,
                    null,
                    $childProduct->getData('short_description') ?? '',
                    $images
                );
            }
        } else {

            $images = [];
            foreach ($product->getMediaGalleryImages() as $image) {
                $images[] = new Image(
                    $image->getData('url'),
                    (int)$image->getData('position')
                );
            }

            $variants[] = new Variant(
                (int)$product->getId(),
                $product->getName(),
                $product->getSku(),
                (float)$product->getPrice(),
                (float)$product->getPrice(),
                null,
                null,
                0,
                0,
                null,
                $product->getData('short_description') ?? '',
                $images
            );
        }

        $categories = [];

        foreach ($product->getCategoryIds() as $id) {
            $category = $this->categoryRepository->get($id, (int) $scope->getScopeId());

            $categories[] = new Category(
                (int)$category->getId(),
                (int)$category->getParentId(),
                $category->getName()
            );
        }

        $productDTO = new Product(
            (int)$product->getId(),
            $product->getName(),
            $product->getTypeId(),
            $product->setStoreId($scope->getScopeId())->getUrlModel()->getUrlInStore($product),
            '',
            $categories,
            $variants,
            $product->getCreatedAt(),
            $product->getUpdatedAt()
        );

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $productDTO->toApiRequest()
        );
    }
}
