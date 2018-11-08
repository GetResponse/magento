<?php

namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GetResponse\GetResponseIntegration\Helper\Config;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use GrShareCode\ContactList\Autoresponder;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class GetResponse extends Template
{
    /** @var ManagerInterface */
    protected $messageManager;

    /** @var RedirectFactory */
    protected $redirectFactory;

    /** @var GetresponseApiClientFactory */
    protected $apiClientFactory;

    /**
     * @param Exception $e
     * @return Redirect
     */
    protected function handleException(Exception $e)
    {
        $this->messageManager->addErrorMessage($e->getMessage());
        return $this->redirectFactory->create()->setPath(Config::PLUGIN_MAIN_PAGE);
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
}
