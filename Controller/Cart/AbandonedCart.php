<?php

namespace GetResponse\GetResponseIntegration\Controller\Cart;

use Magento\Framework\Url;

class AbandonedCart extends \Magento\Framework\App\Action\Action
    implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdDecoder
     */
    protected $cartIdDecoder;

    public function __construct(
        \Magento\Framework\App\Action\Context                                          $context,
        \Magento\Checkout\Helper\Cart                                                  $cartHelper,
        \Magento\Framework\Message\ManagerInterface                                    $messageManager,
        \Magento\Framework\UrlInterface                                                $url,
        \Magento\Checkout\Model\Session                                                $checkoutSession,
        \Magento\Quote\Model\QuoteFactory                                              $quoteFactory,
        \GetResponse\GetResponseIntegration\Application\GetResponse\Cart\CartIdDecoder $cartIdDecoder
    )
    {
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->cartIdDecoder = $cartIdDecoder;
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
        $cartId = $this->cartIdDecoder->decode($cartId);
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
