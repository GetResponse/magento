<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand\Dto\ExportOnDemandDto;
use GetResponse\GetResponseIntegration\Helper\Message;

class ExportOnDemandValidator
{
    private $errorMessage;
    private $customFieldsMappingValidator;

    public function __construct(CustomFieldsMappingValidator $customFieldsMappingValidator)
    {
        $this->customFieldsMappingValidator = $customFieldsMappingValidator;
    }

    public function isValid(ExportOnDemandDto $exportOnDemandDto): bool
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

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}