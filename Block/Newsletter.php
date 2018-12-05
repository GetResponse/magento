<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;

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
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger,
        Repository $repository
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $redirectFactory,
            $apiClientFactory,
            $logger
        );
        $this->repository = $repository;
    }

    /**
     * @return ContactListCollection|Redirect
     */
    public function getLists()
    {
        try {
            return (new ContactListService($this->getApiClientFactory()->createGetResponseApiClient()))->getAllContactLists();
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return NewsletterSettings
     */
    public function getNewsletterSettings()
    {
        return NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );
    }
}
