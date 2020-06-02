<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Export\ExportContactService;
use GrShareCode\Export\ExportContactServiceFactory;

class ExportServiceFactory
{
    private $shareCodeRepository;
    private $apiClientFactory;
    private $exportContactServiceFactory;

    public function __construct(
        ApiClientFactory $apiClientFactory,
        ShareCodeRepository $shareCodeRepository,
        ExportContactServiceFactory $exportContactServiceFactory
    ) {
        $this->apiClientFactory = $apiClientFactory;
        $this->shareCodeRepository = $shareCodeRepository;
        $this->exportContactServiceFactory = $exportContactServiceFactory;
    }

    /**
     * @param $scope
     * @return ExportContactService
     * @throws ApiException
     */
    public function create(Scope $scope): ExportContactService
    {
        return $this->exportContactServiceFactory->create(
            $this->apiClientFactory->createGetResponseApiClient($scope),
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }
}