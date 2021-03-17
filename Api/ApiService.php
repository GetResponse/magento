<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\LiveSynchronization;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderAddressInterface;
use \Magento\Sales\Model\Order as MagentoOrder;

class ApiService
{
    private $repository;
    private $cartHelper;
    private $httpClient;

    public function __construct(
        Repository $repository,
        CartHelper $cartHelper,
        HttpClient $httpClient
    ) {
        $this->repository = $repository;
        $this->cartHelper = $cartHelper;
        $this->httpClient = $httpClient;
    }

    /**
     * @throws HttpClientException
     */
    public function sendCart(Quote $quote, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $cart = $this->createCart($quote);

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $cart->toApiRequest()
        );
    }

    public function sendOrder(MagentoOrder $magentoOrder, Scope $scope): void
    {
        $liveSynchronization = LiveSynchronization::createFromRepository(
            $this->repository->getLiveSynchronization($scope->getScopeId())
        );

        if (!$liveSynchronization->isActive()) {
            return;
        }

        $order = $this->createOrder($magentoOrder);

        $this->httpClient->post(
            $liveSynchronization->getCallbackUrl(),
            $order->toApiRequest()
        );
    }

    private function createOrder(MagentoOrder $order): Order
    {
        return new Order(
            (int)$order->getId(),
            (int)$order->getQuoteId(),
            $order->getCustomerEmail(),
            $this->createCustomerFromOrder($order),
            $this->createLinesFromOrder($order),
            '',
            (float)$order->getGrandTotal(),
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
    }

    private function createCart(Quote $quote): Cart
    {
        return new Cart(
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
}
