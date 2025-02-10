<?php

namespace GetResponse\GetResponseIntegration\Router;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;

class Router implements RouterInterface
{
    const ABANDON_CART_ROUTE = 'abandonCart';
    /**
     * @var ActionFactory $actionFactory
     */
    private $actionFactory;

    /**
     * @var ResponseInterface $response
     */
    private $response;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory     $actionFactory,
        ResponseInterface $response
    )
    {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
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
