<?php
namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Block
 */
class Registration extends GetResponse
{
    /** @var CustomFieldService */
    private $customFieldService;

    /** @var CustomFieldsMappingService */
    private $customFieldsMappingService;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     * @param Repository $repository
     * @param CustomFieldService $customFieldService
     * @param CustomFieldsMappingService $customFieldsMappingService
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger,
        Repository $repository,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $redirectFactory,
            $apiClientFactory,
            $logger
        );
        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->repository = $repository;
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function getCampaigns()
    {
        return (new ContactListService($this->getApiClientFactory()->createGetResponseApiClient()))->getAllContactLists();
    }

    /**
     * @return CustomFieldsMappingCollection
     */
    public function getCustomFieldsMapping()
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration()
        );

    }

    /**
     * @return SubscribeViaRegistration
     */
    public function getRegistrationSettings()
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );

    }

    /**
     * @return array|Redirect
     */
    public function getCustomFieldsFromGetResponse()
    {
        $result = [];

        try {
            foreach ($this->customFieldService->getCustomFields() as $customField) {
                $result[] = [
                    'id' => $customField->getId(),
                    'name' => $customField->getName(),
                ];
            }

            return $result;
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return MagentoCustomerAttributeCollection|Redirect
     */
    public function getMagentoCustomerAttributes()
    {
        try {
            return $this->customFieldsMappingService->getMagentoCustomerAttributes();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
