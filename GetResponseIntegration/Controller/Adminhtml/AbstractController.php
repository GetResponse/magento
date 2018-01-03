<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml
 */
abstract class AbstractController extends Action
{
    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param RepositoryValidator $repositoryValidator
     */
    public function __construct(
        Context $context,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface
     */
    public function checkGetResponseConnection()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);

            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }
        return null;
    }
}
