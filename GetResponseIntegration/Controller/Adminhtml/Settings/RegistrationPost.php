<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
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
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class RegistrationPost extends Action
{
    const BACK_URL = 'getresponseintegration/settings/registration';

    const INVALID_CUSTOM_FIELD_MESSAGE = 'There is a problem with one of your custom field name! Field name must be composed using up to 32 characters, only a-z (lower case), numbers and "_".';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Http */
    private $request;

    /** @var Repository */
    private $repository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

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
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        if (empty($data)) {
            return $resultRedirect;
        }

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $cycleDay = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['cycle_day'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        if (!$isEnabled) {
            $this->repository->clearRegistrationSettings();
        } else {

            $campaignId = $data['campaign_id'];

            if (empty($campaignId)) {
                $this->messageManager->addErrorMessage('You need to select contact list');

                return $resultRedirect;
            }

            if ($updateCustomFields) {
                $customs = CustomFieldFactory::createFromArray($data);

                foreach ($customs as $field => $name) {
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                        $this->messageManager->addErrorMessage(self::INVALID_CUSTOM_FIELD_MESSAGE);

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

        $this->messageManager->addSuccessMessage('Settings saved');

        return $resultRedirect;
    }
}
