<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;

/**
 * Class ExportOnDemandDtoFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto
 */
class ExportOnDemandDtoFactory
{
    /** @var CustomFieldMappingDtoCollection */
    private $customFieldMappingDtoCollection;

    /**
     * @param CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
     */
    public function __construct(CustomFieldMappingDtoCollection $customFieldMappingDtoCollection)
    {
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
    }

    /**
     * @param array $requestData
     * @return ExportOnDemandDto
     * @throws InvalidPrefixException
     */
    public function createFromRequest(array $requestData)
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