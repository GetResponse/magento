<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Command\ExportContactCommandFactory;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Api\Exception\GetresponseApiException;
use Magento\Newsletter\Model\Subscriber;

class ExportOnDemandService
{
    private $exportServiceFactory;
    private $exportContactCommandFactory;

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
     * @param Scope $scope
     * @throws ApiException
     * @throws GetresponseApiException
     */
    public function export(
        Subscriber $subscriber,
        ExportOnDemand $exportOnDemand,
        Scope $scope
    ) {
        $grExportService = $this->exportServiceFactory->create($scope);

        $grExportService->exportContact(
            $this->exportContactCommandFactory->createForSubscriber($subscriber, $exportOnDemand)
        );
    }
}
