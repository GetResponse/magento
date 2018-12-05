<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDtoFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemand;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\ExportOnDemandValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class Process
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Process extends AbstractController
{
    const PAGE_TITLE = 'Export Customer Data on Demand';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    /** @var ExportOnDemandValidator */
    private $exportOnDemandValidator;

    /** @var ExportOnDemandService */
    private $exportOnDemandService;

    /** @var ExportOnDemandDtoFactory */
    private $exportOnDemandDtoFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param ExportOnDemandValidator $exportOnDemandValidator
     * @param ExportOnDemandService $exportOnDemandService
     * @param ExportOnDemandDtoFactory $exportOnDemandDtoFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        ExportOnDemandValidator $exportOnDemandValidator,
        ExportOnDemandService $exportOnDemandService,
        ExportOnDemandDtoFactory $exportOnDemandDtoFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->exportOnDemandValidator = $exportOnDemandValidator;
        $this->exportOnDemandService = $exportOnDemandService;
        $this->exportOnDemandDtoFactory = $exportOnDemandDtoFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     * @throws InvalidPrefixException
     */
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

        $subscribers = $this->repository->getFullCustomersDetails();
        $exportOnDemand = ExportOnDemand::createFromDto($exportOnDemandDto);

        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            try {
                $this->exportOnDemandService->export($subscriber, $exportOnDemand);
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
