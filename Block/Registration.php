<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Block
 */
class Registration extends GetResponse
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
    public function getCampaigns()
    {
        try {
            return (new ContactListService($this->apiClientFactory->createGetResponseApiClient()))->getAllContactLists();
        }catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return CustomFieldsCollection
     */
    public function getCustoms()
    {
        return CustomFieldsCollectionFactory::createFromRepository($this->repository->getCustoms());
    }

    public function getRegistrationSettings()
    {
        return RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );
    }
}
