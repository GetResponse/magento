<?php

namespace GetResponse\GetResponseIntegration\Controller\Cart;

use GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdEncryptor;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\QuoteFactory;

class AbandonedCart extends Action implements HttpGetActionInterface
{
    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CartIdEncryptor
     */
    protected $cartIdEncryptor;

    public function __construct(
        Context          $context,
        Cart             $cartHelper,
        ManagerInterface $messageManager,
        UrlInterface     $url,
        Session          $checkoutSession,
        QuoteFactory     $quoteFactory,
        CartIdEncryptor  $cartIdEncryptor
    )
    {
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->cartIdEncryptor = $cartIdEncryptor;
    }


    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (isset($params['cartId'])) {
            return $this->executeWithCartId((string)$params['cartId']);
        }

        return $this->_redirect($this->url->getUrl('noroute'));
    }


    private function executeWithCartId(string $cartId)
    {
        $cartId = $this->cartIdEncryptor->decrypt($cartId);
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($cartId);

        if (empty($quote->getItems())) {
            $this->messageManager->addErrorMessage(__("Cannot recover an empty or inactive cart"));
            return $this->_redirect($this->cartHelper->getCartUrl());
        }

        $quote->setReservedOrderId(null);
        $quote->setIsActive(true);
        $quote->removePayment();
        $quote->save();

        $this->checkoutSession->clearStorage();
        $this->checkoutSession->replaceQuote($quote);

        return $this->_redirect($this->cartHelper->getCartUrl());
    }
}
