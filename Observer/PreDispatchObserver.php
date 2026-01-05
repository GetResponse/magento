<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Observer;

use Exception;
use GetResponse\GetResponseIntegration\Helper\Route;
use GetResponse\GetResponseIntegration\Logger\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

class PreDispatchObserver implements ObserverInterface
{
    private $urlInterface;
    private $actionFlag;
    private $logger;

    public function __construct(
        UrlInterface $urlInterface,
        ActionFlag $actionFlag,
        Logger $logger
    ) {
        $this->urlInterface = $urlInterface;
        $this->actionFlag = $actionFlag;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer): PreDispatchObserver
    {
        try {
            if (!$this->amIOnTransitionPage()) {
                $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                $observer->getControllerAction()->getResponse()->setRedirect(
                    $this->urlInterface->getUrl(Route::TRANSITION_PAGE_ROUTE)
                );
            }
        } catch (Exception $e) {
            $this->logger->addError($e->getMessage(), ['exception' => $e]);
        }

        return $this;
    }

    private function amIOnTransitionPage(): bool
    {
        return (bool)preg_match('/getresponse\/transition/i', $this->urlInterface->getCurrentUrl());
    }
}
