<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDto;
use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class ExportOnDemandValidator
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportOnDemandValidator
{
    /** @var string */
    private $errorMessage;

    /** @var CustomFieldsMappingValidator */
    private $customFieldsMappingValidator;

    /**
     * @param CustomFieldsMappingValidator $customFieldsMappingValidator
     */
    public function __construct(CustomFieldsMappingValidator $customFieldsMappingValidator)
    {
        $this->customFieldsMappingValidator = $customFieldsMappingValidator;
    }

    /**
     * @param ExportOnDemandDto $exportOnDemandDto
     * @return bool
     */
    public function isValid(ExportOnDemandDto $exportOnDemandDto)
    {
        if (empty($exportOnDemandDto->getContactListId())) {
            $this->errorMessage = Message::SELECT_CONTACT_LIST;

            return false;
        }

        if ($exportOnDemandDto->isAutoresponderEnabled() && null === $exportOnDemandDto->getDayOfCycle()) {
            $this->errorMessage = Message::SELECT_AUTORESPONDER_DAY;

            return false;
        }

        if ($exportOnDemandDto->isEcommerceEnabled() && null === $exportOnDemandDto->getShopId()) {
            $this->errorMessage = Message::STORE_CHOOSE;

            return false;
        }

        if (!$this->customFieldsMappingValidator->isValid($exportOnDemandDto->getCustomFieldMappingDtoCollection())) {
            $this->errorMessage = $this->customFieldsMappingValidator->getErrorMessage();

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}