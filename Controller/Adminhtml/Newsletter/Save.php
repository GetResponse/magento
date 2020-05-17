<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Newsletter;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Backend\App\Action\Context;

class Save extends AbstractController
{
    const BACK_URL = 'getresponse/newsletter/index';

    private $repository;
    private $magentoStore;

    public function __construct(
        Context $context,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->magentoStore = $magentoStore;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::BACK_URL);

        $data = $this->request->getPostValue();

        $autoresponder = (isset($data['gr_autoresponder']) && (int) $data['gr_autoresponder'] === 1) ? $data['autoresponder'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 === (int) $data['gr_enabled'];

        if (!$isEnabled) {
            $this->repository->clearNewsletterSettings($this->magentoStore->getStoreIdFromUrl());
        } else {
            $campaignId = $data['campaign_id'];

            if (empty($campaignId)) {
                $this->messageManager->addErrorMessage(Message::SELECT_CONTACT_LIST);

                return $resultRedirect;
            }

            $newsletterSettings = NewsletterSettingsFactory::createFromArray([
                'status' => $isEnabled,
                'campaignId' => $campaignId,
                'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : '',
                'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : '',
            ]);

            $this->repository->saveNewsletterSettings(
                $newsletterSettings,
                $this->magentoStore->getStoreIdFromUrl()
            );
        }

        $this->messageManager->addSuccessMessage(Message::SETTINGS_SAVED);
        return $resultRedirect;
    }
}
