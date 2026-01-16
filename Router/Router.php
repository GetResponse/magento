<?php

namespace GetResponse\GetResponseIntegration\Router;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;

class Router implements RouterInterface
{
    public const ABANDON_CART_ROUTE = 'abandonCart';
    /**
     * @var ActionFactory $actionFactory
     */
    private $actionFactory;

    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        // Abandoned cart
        if (strpos($identifier, self::ABANDON_CART_ROUTE) !== false) {
            $request->setModuleName('getresponse');
            $request->setControllerName('cart');
            $request->setActionName('abandonedcart');

            return $this->actionFactory->create(
                Forward::class,
                ['request' => $request]
            );
        }

        return null;
    }
}
