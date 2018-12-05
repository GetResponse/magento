<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Command\ExportContactCommandFactory;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class ExportOnDemandService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportOnDemandService
{
    /** @var ExportServiceFactory */
    private $exportServiceFactory;

    /** @var ExportContactCommandFactory */
    private $exportContactCommandFactory;

    /**
     * @param ExportContactCommandFactory $exportContactCommandFactory
     * @param ExportServiceFactory $exportServiceFactory
     */
    public function __construct(
        ExportContactCommandFactory $exportContactCommandFactory,
        ExportServiceFactory $exportServiceFactory
    ) {
        $this->exportServiceFactory = $exportServiceFactory;
        $this->exportContactCommandFactory = $exportContactCommandFactory;
    }

    /**
     * @param Subscriber $subscriber
     * @param ExportOnDemand $exportOnDemand
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function export(Subscriber $subscriber, ExportOnDemand $exportOnDemand)
    {
        $grExportService = $this->exportServiceFactory->create();

        $grExportService->exportContact(
            $this->exportContactCommandFactory->createForSubscriber($subscriber, $exportOnDemand)
        );
    }
}