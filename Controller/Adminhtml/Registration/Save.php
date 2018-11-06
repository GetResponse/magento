<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Registration;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldFactoryException;
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
use GetResponse\GetResponseIntegration\Helper\Config;

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
    ) {
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
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        $updateCustomFields = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
        $autoresponder = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1) ? $data['autoresponder'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 == $data['gr_enabled'] ? true : false;

        try {
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
                    'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : '',
                    'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : '',
                ]);

                $this->repository->saveRegistrationSettings($registrationSettings);
            }
            $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);
            return $resultRedirect;
        } catch (CustomFieldFactoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect;
        }
    }
}
