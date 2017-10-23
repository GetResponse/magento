<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CampaignFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Getresponse\Repository as GrRepository;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Create
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Create extends Action
{
    const PAGE_TITLE = 'New Contact List';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->buildRepository();
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Config::INCORRECT_API_RESOONSE_MESSAGE);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $backUrl = $this->getRequest()->getParam('back_url');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        // validator
        $error = $this->validateNewListParams($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        }

        $lang = substr($this->repository->getMagentoCountryCode(), 0, 2);
        $result = $this->grRepository->createCampaign(
            CampaignFactory::buildFromUserPayload($data, $lang)
        );

        if (isset($result->httpStatus) && (int)$result->httpStatus >= 400) {
            $this->messageManager->addErrorMessage(isset($result->codeDescription) ? $result->codeDescription . ' - uuid: ' . $result->uuid : 'Something goes wrong');
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);
            return $resultPage;
        } else {
            $this->messageManager->addSuccessMessage('List created');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($backUrl);
            return $resultRedirect;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function validateNewListParams($data)
    {
        if (strlen($data['campaign_name']) < 3) {
            return 'You need to enter a name that\'s at least 3 characters long';
        }

        if (strlen($data['from_field']) === 0) {
            return 'You need to select a sender email address';
        }

        if (strlen($data['reply_to_field']) === 0) {
            return 'Reply-To is a required field';
        }

        if (strlen($data['confirmation_subject']) === 0) {
            return 'Confirmation subject is a required field';
        }

        if (strlen($data['confirmation_body']) === 0) {
            return 'Confirmation body is a required field';
        }

        return '';
    }
}
