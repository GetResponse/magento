<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDtoFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemand;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\CustomerReadModel;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\Subscriber;

class Process extends AbstractController
{
    const PAGE_TITLE = 'Export Customer Data on Demand';

    protected $resultPageFactory;
    private $exportOnDemandValidator;
    private $exportOnDemandService;
    private $exportOnDemandDtoFactory;
    private $magentoStore;
    private $customerReadModel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ExportOnDemandValidator $exportOnDemandValidator,
        ExportOnDemandService $exportOnDemandService,
        ExportOnDemandDtoFactory $exportOnDemandDtoFactory,
        MagentoStore $magentoStore,
        CustomerReadModel $customerReadModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->exportOnDemandValidator = $exportOnDemandValidator;
        $this->exportOnDemandService = $exportOnDemandService;
        $this->exportOnDemandDtoFactory = $exportOnDemandDtoFactory;
        $this->magentoStore = $magentoStore;
        $this->customerReadModel = $customerReadModel;
    }

    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();

        $exportOnDemandDto = $this->exportOnDemandDtoFactory->createFromRequest($request->getPostValue());

        if (!$this->exportOnDemandValidator->isValid($exportOnDemandDto)) {
            $this->messageManager->addErrorMessage($this->exportOnDemandValidator->getErrorMessage());
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $subscribers = $this->customerReadModel->findCustomers();
        $exportOnDemand = ExportOnDemand::createFromDto($exportOnDemandDto);

        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            try {
                $this->exportOnDemandService->export(
                    $subscriber,
                    $exportOnDemand,
                    $this->magentoStore->getStoreIdFromUrl()
                );
            } catch (GetresponseApiException $e) {
            } catch (ApiException $e) {
            }
        }

        $this->messageManager->addSuccessMessage(Message::DATA_EXPORTED);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
