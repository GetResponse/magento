<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;

class Save extends AbstractController
{
    const BACK_URL = 'getresponse/registration/index';

    private $repository;
    private $customFieldsMappingValidator;
    private $subscribeViaRegistrationService;
    private $customFieldMappingDtoCollection;
    private $magentoStore;

    public function __construct(
        Context $context,
        Repository $repository,
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection,
        CustomFieldsMappingValidator $customFieldsMappingValidator,
        SubscribeViaRegistrationService $subscribeViaRegistrationService,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
        $this->customFieldsMappingValidator = $customFieldsMappingValidator;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->magentoStore = $magentoStore;
        $this->request = $this->getRequest();
    }

    /**
     * @return ResponseInterface|Redirect
     * @throws InvalidPrefixException
     */
    public function execute()
    {
        $scope = new Scope($this->magentoStore->getStoreIdFromUrl());
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $autoresponder = (isset($data['gr_autoresponder']) && ((int)$data['gr_autoresponder'] === 1)) ? $data['autoresponder'] : '';

        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->clearRegistrationSettings(
                $this->magentoStore->getStoreIdFromUrl()
            );

            $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);
            return $resultRedirect;
        }

        $campaignId = $data['campaign_id'];

        if (empty($campaignId)) {
            $this->messageManager->addErrorMessage(Message::SELECT_CONTACT_LIST);

            return $resultRedirect;
        }

        $customFieldMappingDtoCollection = $this->customFieldMappingDtoCollection->createFromRequestData($data);

        if (!$this->customFieldsMappingValidator->isValid($customFieldMappingDtoCollection)) {
            $this->messageManager->addErrorMessage($this->customFieldsMappingValidator->getErrorMessage());

            return $resultRedirect;
        }

        $this->subscribeViaRegistrationService->saveCustomFieldsMapping(
            CustomFieldsMappingCollection::createFromDto($customFieldMappingDtoCollection),
            $scope
        );

        $registrationSettings = SubscribeViaRegistrationFactory::createFromArray([
            'status' => $isEnabled,
            'customFieldsStatus' => $updateCustomFields,
            'campaignId' => $campaignId,
            'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : null,
            'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : null,
        ]);

        $this->subscribeViaRegistrationService->saveSettings($registrationSettings, $scope);
        $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);

        return $resultRedirect;
    }
}
