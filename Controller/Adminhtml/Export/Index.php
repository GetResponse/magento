<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class Index
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Export
 */
class Index extends AbstractController
{
    const PAGE_TITLE = 'Export Customer Data on Demand';

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface|Page
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
