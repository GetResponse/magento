<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class AdminCheckAuthorization
 * @package GetResponse\GetResponseIntegration\Observer
 */
class AdminCheckAuthorization implements ObserverInterface
{
    /** @var UrlInterface */
    private $urlInterface;

    /** @var Repository */
    private $magentoRepository;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var ActionFlag */
    private $actionFlag;

    /**
     * @param UrlInterface $urlInterface
     * @param Repository $magentoRepository
     * @param ManagerInterface $messageManager
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        UrlInterface $urlInterface,
        Repository $magentoRepository,
        ManagerInterface $messageManager,
        ActionFlag $actionFlag
    ) {
        $this->magentoRepository = $magentoRepository;
        $this->urlInterface = $urlInterface;
        $this->messageManager = $messageManager;
        $this->actionFlag = $actionFlag;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if ($this->isCurrentUrlWhitelisted()) {
            return $this;
        }

        $settings = $this->magentoRepository->getConnectionSettings();

        if (empty($settings)) {
            $this->messageManager->addErrorMessage(Message::CONNECT_TO_GR);
            $url = $this->urlInterface->getUrl(Config::PLUGIN_MAIN_PAGE);
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $observer->getControllerAction()->getResponse()->setRedirect($url);
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isCurrentUrlWhitelisted()
    {
        return (bool) preg_match('/getresponse\/account/i', $this->urlInterface->getCurrentUrl());
    }
}
