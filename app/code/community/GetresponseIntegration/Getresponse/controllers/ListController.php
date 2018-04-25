<?php

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_ListController
 */
class GetresponseIntegration_Getresponse_ListController extends GetresponseIntegration_Getresponse_BaseController
{
    /**
     * GET getresponse/list/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('New List'))->_title($this->__('GetResponse'));

        $langCode = strtoupper(substr(Mage::app()->getLocale()->getDefaultLocale(), 0, 2));

        try {
            $fromFields = $this->api->getFromFields();
            $confirmationSubject = $this->api->getSubscriptionConfirmationsSubject($langCode);
            $confirmationBody = $this->api->getSubscriptionConfirmationsBody($langCode);
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            $fromFields = $confirmationSubject = $confirmationBody = array();
        }

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/create_list.phtml')
            ->assign('from_fields', $fromFields)
            ->assign('confirmation_subject', $confirmationSubject)
            ->assign('confirmation_body', $confirmationBody)
            ->assign('back_url', base64_decode($this->getRequest()->getParam('back_url')))
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/list/save
     */
    public function saveAction()
    {
        $this->_initAction();
        $params = $this->getRequest()->getParams();

        $error = $this->validateNewListParameters($params);

        if (strlen($error > 0)) {
            $this->_getSession()->addError($error);
            $this->_redirect($params['back_url']);
            return;
        }

        $campaignName = strtolower($params['campaign_name']);

        try {
            $this->api->addCampaignToGetResponse(
                $campaignName,
                $params['from'],
                $params['reply_to'],
                $params['confirmation_subject'],
                $params['confirmation_body']
            );

            $this->_getSession()->addSuccess('List created');
            $this->_redirect($params['back_url']);
        } catch (Exception $e) {

            if ($e->getCode() === 1008) {
                $this->_getSession()->addError('List name you entered already exists. Please enter a different name');
            } else {

                $this->_getSession()
                    ->addError(
                        'List "' . $campaignName . '" has not been added'
                        . ' - ' . $e->getMessage() . '.'
                    );
            }
            $this->_redirect('*/*/index');
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function validateNewListParameters($params)
    {
        if (!isset($params['campaign_name']) || strlen($params['campaign_name']) < 3) {
            return 'You need to enter a name that\'s at least 3 characters long';
        }

        if (!isset($params['from']) || strlen($params['from']) === 0) {
            return 'You need to select a sender email address';
        }

        if (!isset($params['reply_to']) || strlen($params['reply_to']) === 0) {
            return 'You need to select reply to';
        }

        if (!isset($params['confirmation_subject']) || strlen($params['confirmation_subject']) === 0) {
            return 'You need to select a subject line for the subscription confirmation message';
        }

        if (!isset($params['confirmation_body']) || strlen($params['confirmation_body']) === 0) {
            return 'You need to select confirmation message body template';
        }

        return '';
    }
}
