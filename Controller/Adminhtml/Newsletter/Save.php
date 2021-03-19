<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Newsletter;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;

class Save extends AbstractController
{
    private $repository;

    public function __construct(Context $context, Repository $repository)
    {
        parent::__construct($context);
        $this->repository = $repository;
    }

    public function execute()
    {
        parent::execute();

        $data = $this->request->getPostValue();

        $autoresponder = (isset($data['gr_autoresponder']) && (int) $data['gr_autoresponder'] === 1) ? $data['autoresponder'] : '';
        $isEnabled = isset($data['gr_enabled']) && 1 === (int) $data['gr_enabled'];

        if (!$isEnabled) {
            $this->repository->clearNewsletterSettings($this->scope->getScopeId());
        } else {
            $campaignId = $data['campaign_id'];

            if (empty($campaignId)) {
                return $this->redirect(
                    $this->_redirect->getRefererUrl(),
                    Message::SELECT_CONTACT_LIST,
                    true
                );
            }

            $newsletterSettings = NewsletterSettingsFactory::createFromArray([
                'status' => $isEnabled,
                'campaignId' => $campaignId,
                'cycleDay' => !empty($autoresponder) ? explode('_', $autoresponder)[0] : '',
                'autoresponderId' => !empty($autoresponder) ? explode('_', $autoresponder)[1] : '',
            ]);

            $this->repository->saveNewsletterSettings($newsletterSettings, $this->scope->getScopeId());
        }

        return $this->redirect($this->_redirect->getRefererUrl(), Message::SETTINGS_SAVED);
    }
}
