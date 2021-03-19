<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;

class Save extends AbstractController
{
    private $repository;
    private $customFieldsMappingValidator;
    private $subscribeViaRegistrationService;
    private $customFieldMappingDtoCollection;

    public function __construct(
        Context $context,
        Repository $repository,
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection,
        CustomFieldsMappingValidator $customFieldsMappingValidator,
        SubscribeViaRegistrationService $subscribeViaRegistrationService
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
        $this->customFieldsMappingValidator = $customFieldsMappingValidator;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
    }

    public function execute()
    {
        parent::execute();

        $data = $this->request->getPostValue();

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $autoresponder = (isset($data['gr_autoresponder']) && ((int)$data['gr_autoresponder'] === 1)) ? $data['autoresponder'] : '';

        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->clearRegistrationSettings($this->scope->getScopeId());

            return $this->redirect($this->_redirect->getRefererUrl(), Message::SETTINGS_SAVED);
        }

        $campaignId = $data['campaign_id'];

        if (empty($campaignId)) {

            return $this->redirect($this->_redirect->getRefererUrl(), Message::SELECT_CONTACT_LIST, true);
        }

        $customFieldMappingDtoCollection = $this->customFieldMappingDtoCollection->createFromRequestData($data);

        if (!$this->customFieldsMappingValidator->isValid($customFieldMappingDtoCollection)) {
            return $this->redirect(
                $this->_redirect->getRefererUrl(),
                $this->customFieldsMappingValidator->getErrorMessage(),
                true
            );
        }

        $this->subscribeViaRegistrationService->saveCustomFieldsMapping(
            CustomFieldsMappingCollection::createFromDto($customFieldMappingDtoCollection),
            $this->scope
        );

        $registrationSettings = SubscribeViaRegistrationFactory::createFromArray([
            'status' => $isEnabled,
            'customFieldsStatus' => $updateCustomFields,
            'campaignId' => $campaignId,
            'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : null,
            'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : null,
        ]);

        $this->subscribeViaRegistrationService->saveSettings($registrationSettings, $this->scope);

        return $this->redirect($this->_redirect->getRefererUrl(), Message::SETTINGS_SAVED);
    }
}
