<?php

namespace GetResponse\GetResponseIntegration\Router;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    private $actionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory     $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    )
    {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        // Abandoned cart
        if (strpos($identifier, 'abandonCart') !== false) {
            $request->setModuleName('getresponse');
            $request->setControllerName('cart');
            $request->setActionName('abandonedcart');

            return $this->actionFactory->create(
                \Magento\Framework\App\Action\Forward::class,
                ['request' => $request]
            );
        }

        return null;
    }
}
