<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GetResponse\GetResponseIntegration\Domain\Magento\ShareCodeRepository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Contact\ContactService as GrContactService;
use GrShareCode\Contact\ContactServiceFactory as GrContactServiceFactory;

class ContactServiceFactory
{
    private $shareCodeRepository;
    private $apiClientFactory;
    private $grContactServiceFactory;

    public function __construct(
        ShareCodeRepository $shareCodeRepository,
        ApiClientFactory $apiClientFactory,
        GrContactServiceFactory $grContactServiceFactory
    ) {
        $this->shareCodeRepository = $shareCodeRepository;
        $this->apiClientFactory = $apiClientFactory;
        $this->grContactServiceFactory = $grContactServiceFactory;
    }

    /**
     * @param Scope $scope
     * @return GrContactService
     * @throws ApiException
     */
    public function create(Scope $scope): GrContactService
    {
        $getResponseApi = $this->apiClientFactory->createGetResponseApiClient($scope);

        return $this->grContactServiceFactory->create(
            $getResponseApi,
            $this->shareCodeRepository,
            Config::ORIGIN_NAME
        );
    }
}
