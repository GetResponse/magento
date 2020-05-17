<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

abstract class AbstractController extends Action
{
    protected $request;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->request = $this->getRequest();
    }

    public function _redirect($path, $arguments = [])
    {
        $scope = $this->request->getParam(Config::SCOPE_TAG);

        if (!empty($scope)) {
            $path .= '/' . Config::SCOPE_TAG . '/' . $scope;
        }

        return parent::_redirect($path);
    }

    public function redirectToStore(string $path): ResponseInterface
    {
        $storeId = $this->_session->getGrScope();
        $path .= '/' . Config::SCOPE_TAG . '/' . $storeId;
        return parent::_redirect($path);
    }
}
