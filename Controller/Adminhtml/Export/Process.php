<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use Exception;
use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDtoFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemand;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;

class Process extends AbstractController
{
    private $exportOnDemandValidator;
    private $exportOnDemandService;
    private $exportOnDemandDtoFactory;
    private $customerReadModel;

    public function __construct(
        Context $context,
        ExportOnDemandValidator $exportOnDemandValidator,
        ExportOnDemandService $exportOnDemandService,
        ExportOnDemandDtoFactory $exportOnDemandDtoFactory,
        CustomerReadModel $customerReadModel
    ) {
        parent::__construct($context);
        $this->exportOnDemandValidator = $exportOnDemandValidator;
        $this->exportOnDemandService = $exportOnDemandService;
        $this->exportOnDemandDtoFactory = $exportOnDemandDtoFactory;
        $this->customerReadModel = $customerReadModel;
    }

    public function execute()
    {
        parent::execute();

        if (!$this->isConnected()) {
            return $this->redirectToStore(Route::ACCOUNT_INDEX_ROUTE);
        }

        $exportOnDemandDto = $this->exportOnDemandDtoFactory->createFromRequest(
            $this->request->getPostValue()
        );

        if (!$this->exportOnDemandValidator->isValid($exportOnDemandDto)) {
            $this->messageManager->addErrorMessage(
                $this->exportOnDemandValidator->getErrorMessage()
            );

            return $this->redirect(
                $this->_redirect->getRefererUrl(),
                $this->exportOnDemandValidator->getErrorMessage(),
                true
            );
        }

        $customers = $this->customerReadModel->findCustomers();
        $exportOnDemand = ExportOnDemand::createFromDto($exportOnDemandDto);

        foreach ($customers as $customer) {
            try {
                $this->exportOnDemandService->export(
                    $customer,
                    $exportOnDemand,
                    $this->scope
                );
            } catch (Exception $e) {}
        }

        return $this->redirect($this->_redirect->getRefererUrl(), Message::DATA_EXPORTED);
    }
}
