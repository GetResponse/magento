<?php
namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\ContactList\Autoresponder;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class GetResponse extends Template
{
    /** @var ManagerInterface */
    private $messageManager;

    /** @var RedirectFactory */
    private $redirectFactory;

    /** @var ApiClientFactory */
    private $apiClientFactory;

    /** @var Logger */
    private $logger;

    /**
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->apiClientFactory = $apiClientFactory;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getAutoRespondersForFrontend()
    {
        $responders = $this->getAutoResponders();
        if (empty($responders)) {
            return [];
        }

        return $responders;
    }

    /**
     * @return array|Redirect
     */
    public function getAutoResponders()
    {
        try {
            $result = [];
            $grApiClient = $this->apiClientFactory->createGetResponseApiClient();

            $service = new ContactListService($grApiClient);
            $responders = $service->getAutoresponders();

            /** @var Autoresponder $responder */
            foreach ($responders as $responder) {
                $result[$responder->getCampaignId()][$responder->getId()] = [
                    'name' => $responder->getName(),
                    'subject' => $responder->getSubject(),
                    'dayOfCycle' => $responder->getCycleDay()
                ];
            }

            return $result;
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param Exception $e
     * @return Redirect
     */
    protected function handleException(Exception $e)
    {
        $this->logger->addError($e->getMessage(), ['exception' => $e]);
        $this->messageManager->addErrorMessage($e->getMessage());

        return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
    }

    /**
     * @return ApiClientFactory
     */
    public function getApiClientFactory()
    {
        return $this->apiClientFactory;
    }

}
