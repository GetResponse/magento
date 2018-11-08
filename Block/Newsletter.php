<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Newsletter
 * @package GetResponse\GetResponseIntegration\Block
 */
class Newsletter extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Repository $repository,
        GetresponseApiClientFactory $apiClientFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getLists()
    {
        try {
            return (new ContactListService($this->apiClientFactory->createGetResponseApiClient()))->getAllContactLists();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getNewsletterSettings()
    {
        return NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );
    }
}
