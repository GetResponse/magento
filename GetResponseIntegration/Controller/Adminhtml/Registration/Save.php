<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

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

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context, $repositoryValidator);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $cycleDay = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['cycle_day'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->clearRegistrationSettings();
        } else {
            $campaignId = $data['campaign_id'];

            if (empty($campaignId)) {
                $this->messageManager->addErrorMessage(Message::SELECT_CONTACT_LIST);

                return $resultRedirect;
            }

            if ($updateCustomFields) {
                $customs = CustomFieldFactory::createFromArray($data);

                foreach ($customs as $field => $name) {
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                        $this->messageManager->addErrorMessage(sprintf(Message::INVALID_CUSTOM_FIELD_VALUE, $name));

                        return $resultRedirect;
                    }
                }

                $customs = CustomFieldsCollectionFactory::createFromUserPayload(
                    $customs,
                    $this->repository->getCustoms()
                );

                $this->repository->updateCustoms($customs);
            }

            $registrationSettings = RegistrationSettingsFactory::createFromArray([
                'status' => $isEnabled,
                'customFieldsStatus' => $updateCustomFields,
                'campaignId' => $campaignId,
                'cycleDay' => $cycleDay
            ]);

            $this->repository->saveRegistrationSettings($registrationSettings);
        }

        $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);

        return $resultRedirect;
    }
}
