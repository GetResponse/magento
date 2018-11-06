<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\GetresponseApiException;
use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Newsletter
 * @package GetResponse\GetResponseIntegration\Block
 */
class Newsletter extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Getresponse */
    private $getResponseBlock;

    /** @var RedirectFactory */
    private $redirectFactory;

    /** @var ManagerInterface */
    private $messageManager;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getResponseBlock
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getResponseBlock,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->getResponseBlock = $getResponseBlock;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getLists()
    {
        try {
            return (new ContactListService($this->repositoryFactory->createGetResponseApiClient()))->getAllContactLists();
        } catch (RepositoryException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        } catch (GetresponseApiException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
        }
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        return $this->getResponseBlock->getAutoRespondersForFrontend();
    }

    public function getNewsletterSettings()
    {
        return $this->getResponseBlock->getNewsletterSettings();
    }
}
