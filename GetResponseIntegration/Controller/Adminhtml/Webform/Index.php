<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform
 */
class Index extends Action
{
    const PAGE_TITLE = 'Add contacts via GetResponse forms';

    /** @var PageFactory */
    protected $resultPageFactory;
    /** @var RepositoryValidator */

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
        try {
            if (!$this->repositoryValidator->validate()) {
                $this->messageManager->addErrorMessage(Message::INCORRECT_API_RESPONSE_MESSAGE);

                return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
            }
        } catch (RepositoryException $e) {
            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(self::PAGE_TITLE);

        return $resultPage;
    }
}
