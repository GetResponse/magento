<?php
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;
use GetresponseIntegration_Getresponse_Domain_WebformFactory as WebformFactory;

require_once 'BaseController.php';

/**
 * Class GetresponseIntegration_Getresponse_WebformController
 */
class GetresponseIntegration_Getresponse_WebformController extends GetresponseIntegration_Getresponse_BaseController
{
    public $layout_positions = array(
        'top.menu' => 'Navigation Bar',
        'after_body_start' => 'Page Top',
        'left' => 'Left Column',
        'right' => 'Right Column',
        'content' => 'Content',
        'before_body_end' => 'Page Bottom',
        'footer' => 'Footer'
    );

    public $block_positions = array(
        'after' => 'Bottom',
        'before' => 'Top',
    );

    /**
     * GET getresponse/webform/index
     */
    public function indexAction()
    {
        $this->_initAction();

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $this->_title($this->__('Subscription via a form'))->_title($this->__('GetResponse'));

        $this->settings->layout_positions = $this->layout_positions;
        $this->settings->block_positions = $this->block_positions;

        try {
            $forms = $this->api->getPublishedForms();
            $webForms = $this->api->getPublishedWebForms();
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            $forms = $webForms = array();
        }

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/viawebform.phtml')
            ->assign('forms', $forms)
            ->assign('webforms', $webForms)
            ->assign('layout_positions', $this->layout_positions)
            ->assign('block_positions', $this->block_positions)
            ->assign('settings', $this->settings)
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/webform/save
     */
    public function saveAction()
    {
        $this->_initAction();

        if (!$this->isConnectedToGetResponse()) {
            $this->redirectToLoginPage();
            return;
        }

        $isEnabled = $this->getRequest()->getParam('active_subscription', 0);
        $params = $this->getRequest()->getParams();
        $webformRepository = new WebformRepository($this->currentShopId);

        if (0 === $isEnabled) {
            $webformRepository->delete();

            $this->_getSession()->addSuccess('Form unpublished');
            $this->_redirect('*/*/index');
            return;
        }

        if (empty($params['webform_id'])) {
            $this->_getSession()->addError('Webform Id can\'t be empty');
            $this->_redirect('*/*/index');
            return;
        }

        if (isset($params['webform_title'])) {
            if ($params['webform_title'] == '') {
                $this->_getSession()->addError('Block Title can\'t be empty');
                $this->_redirect('*/*/index');
                return;
            } elseif (strlen($params['webform_title']) > 255) {
                $this->_getSession()->addError('Title is too long. Max: 255 characters');
                $this->_redirect('*/*/index');
                return;
            }
        }

        try {
            if (!empty($params['gr_webform_type'])
                && $params['gr_webform_type'] == 'old'
            ) {
                $webForm = $this->api->getWebform($params['webform_id']);
            } else {
                $webForm = $this->api->getForm($params['webform_id']);
            }
        } catch (GetresponseIntegration_Getresponse_Domain_GetresponseException $e) {
            $webForm = array();
        }

        if (empty($webForm['codeDescription'])) {
            $data = array(
                'webformId' => $params['webform_id'],
                'activeSubscription' => $isEnabled,
                'layoutPosition' => $params['layout_position'],
                'blockPosition' => $params['block_position'],
                'webformTitle' => trim($params['webform_title']),
                'url' => $webForm['scriptUrl']
            );
            $webform = WebformFactory::createFromArray($data);
            $webformRepository->create($webform);

            $this->_getSession()->addSuccess('Form published');
        } else {
            $this->_getSession()->addError('Error - please try again');
        }

        $this->_redirect('*/*/index');
    }
}
