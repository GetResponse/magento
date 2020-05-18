<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;

class ExportOnDemandDtoFactory
{
    private $customFieldMappingDtoCollection;

    public function __construct(
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
    ) {
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
    }

    /**
     * @param array $requestData
     * @return ExportOnDemandDto
     * @throws InvalidPrefixException
     */
    public function createFromRequest(array $requestData): ExportOnDemandDto
    {
        return new ExportOnDemandDto(
            $requestData['campaign_id'],
            isset($requestData['gr_autoresponder']),
            (isset($requestData['gr_autoresponder']) && $requestData['cycle_day'] !== '') ? (int)$requestData['cycle_day'] : null,
            isset($requestData['ecommerce']) && !empty($requestData['ecommerce']),
            (isset($requestData['ecommerce']) && !empty($requestData['ecommerce']) && !empty($requestData['store_id'])) ? $requestData['store_id'] : null,
            isset($requestData['gr_sync_order_data']),
            $this->customFieldMappingDtoCollection->createFromRequestData($requestData)
        );
    }
}
