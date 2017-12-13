<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use GetResponse\GetResponseIntegration\Domain\GetResponse\ListValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
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
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->createRepository();
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {
            if (!$this->repositoryValidator->validate()) {
                $this->messageManager->addErrorMessage(Message::INCORRECT_API_RESPONSE_MESSAGE);
                return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
            }
        } catch (RepositoryException $e) {
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

        $error = ListValidator::validateNewListParams($data);

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        }

        $data['lang'] = substr($this->repository->getMagentoCountryCode(), 0, 2);
        $result = $this->grRepository->createCampaign(
            CampaignFactory::createFromArray($data)
        );

        if (isset($result->httpStatus) && (int)$result->httpStatus >= 400) {
            $this->messageManager->addErrorMessage(Message::CANNOT_CREATE_LIST . ' - uuid: ' . $result->uuid);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

            return $resultPage;
        } else {
            $this->messageManager->addSuccessMessage(Message::LIST_CREATED);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($backUrl);

            return $resultRedirect;
        }
    }
}
