<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\CustomFieldMappingDtoCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\Dto\InvalidPrefixException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class RegistrationPost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration
 */
class Save extends AbstractController
{
    const BACK_URL = 'getresponse/registration/index';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /** @var CustomFieldsMappingValidator */
    private $customFieldsMappingValidator;

    /** @var SubscribeViaRegistrationService */
    private $subscribeViaRegistrationService;

    /** @var CustomFieldsMappingCollection */
    private $customFieldMappingDtoCollection;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param CustomFieldMappingDtoCollection $customFieldMappingDtoCollection
     * @param CustomFieldsMappingValidator $customFieldsMappingValidator
     * @param SubscribeViaRegistrationService $subscribeViaRegistrationService
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        CustomFieldMappingDtoCollection $customFieldMappingDtoCollection,
        CustomFieldsMappingValidator $customFieldsMappingValidator,
        SubscribeViaRegistrationService $subscribeViaRegistrationService
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->customFieldMappingDtoCollection = $customFieldMappingDtoCollection;
        $this->customFieldsMappingValidator = $customFieldsMappingValidator;
        $this->subscribeViaRegistrationService = $subscribeViaRegistrationService;
        $this->request = $this->getRequest();
    }

    /**
     * @return ResponseInterface|Redirect
     * @throws InvalidPrefixException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $autoresponder = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['autoresponder'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->clearRegistrationSettings();
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
            CustomFieldsMappingCollection::createFromDto($customFieldMappingDtoCollection)
        );

        $registrationSettings = SubscribeViaRegistrationFactory::createFromArray([
            'status' => $isEnabled,
            'customFieldsStatus' => $updateCustomFields,
            'campaignId' => $campaignId,
            'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : null,
            'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : null,
        ]);

        $this->subscribeViaRegistrationService->saveSettings($registrationSettings);
        $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);

        return $resultRedirect;
    }
}
