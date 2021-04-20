<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Webform;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebForm;
use GetResponse\GetResponseIntegration\Helper\Message;
use GetResponse\GetResponseIntegration\Helper\Route;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Cache\Type;

class Save extends AbstractController
{
    private $repository;
    private $cacheTypeList;

    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $cacheTypeList;
        $this->repository = $repository;
    }

    public function execute()
    {
        parent::execute();

        if (!$this->isConnected()) {
            return $this->redirectToStore(Route::ACCOUNT_INDEX_ROUTE);
        }

        $data = $this->request->getPostValue();

        $params = [
            'isEnabled' => isset($data['isEnabled']) && 1 === (int) $data['isEnabled'],
            'url' => $data['url'] ?? '',
            'webFormId' => $data['webformId'] ?? '',
            'place' => $data['sidebar'] ?? ''
        ];

        $webForm = WebForm::createFromArray($params);

        if ($webForm->isEnabled()) {
            $error = $this->validateWebFormData($webForm);

            if (!empty($error)) {
                return $this->redirect($this->_redirect->getRefererUrl(), $error, true);
            }
        }

        $this->repository->saveWebformSettings($webForm, $this->scope->getScopeId());

        $this->cacheTypeList->cleanType(Type::TYPE_IDENTIFIER);
        $message = $webForm->isEnabled() ? Message::FORM_PUBLISHED : Message::FORM_UNPUBLISHED;

        return $this->redirect($this->_redirect->getRefererUrl(), $message);
    }

    /**
     * @param WebForm $webForm
     * @return string|null
     */
    private function validateWebFormData(WebForm $webForm)
    {
        if (empty($webForm->getWebFormId()) && empty($webForm->getSidebar())) {
            return Message::SELECT_FORM_POSITION_AND_PLACEMENT;
        }

        if (empty($webForm->getWebFormId())) {
            return Message::SELECT_FORM;
        }

        if (empty($webForm->getSidebar())) {
            return Message::SELECT_FORM_POSITION;
        }

        return null;
    }
}
