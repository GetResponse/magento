<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Api;

use GetResponse\GetResponseIntegration\Domain\Magento\PluginMode;
use GetResponse\GetResponseIntegration\Domain\Magento\PluginModeException;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\RequestValidationException;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Webapi\Rest\Request;

abstract class ApiController extends Action
{
    /** @var Request; */
    protected $request;
    /** @var Scope */
    protected $scope;
    /** @var MagentoStore */
    protected $magentoStore;
    /** @var Repository */
    protected $repository;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->request = $this->_objectManager->get(Request::class);
        $this->magentoStore = $this->_objectManager->get(MagentoStore::class);
        $this->repository = $this->_objectManager->get(Repository::class);
    }

    /**
     * This method initializes properties used in controllers.
     * @return void
     */
    public function initialize()
    {
        $scopeId = $this->request->getParam('scope');

        if (empty($scopeId)) {
            throw RequestValidationException::create('Missing scope.');
        }

        if (!$this->magentoStore->storeExists((int)$scopeId)) {
            throw RequestValidationException::create('Incorrect scope.');
        }


        $this->scope = new Scope($scopeId);
    }

    /**
     * @throws PluginModeException
     * @return void
     */
    public function verifyPluginMode()
    {
        $pluginMode = PluginMode::createFromRepository($this->repository->getPluginMode($this->scope->getScopeId()));

        if (!$pluginMode->isNewVersion()) {
            throw PluginModeException::createForInvalidPluginMode('Incorrect plugin mode');
        }
    }

    /**
     * @return void
     */
    public function execute()
    {
    }
}
