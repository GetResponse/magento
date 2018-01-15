<?php
use GetresponseIntegration_Getresponse_Domain_WebformRepository as WebformRepository;
use GetresponseIntegration_Getresponse_Domain_WebformFactory as WebformFactory;

require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

class GetresponseIntegration_Getresponse_WebformController extends GetresponseIntegration_Getresponse_BaseController
{
    public $layout_positions = [
        'top.menu' => 'Navigation Bar',
        'after_body_start' => 'Page Top',
        'left' => 'Left Column',
        'right' => 'Right Column',
        'content' => 'Content',
        'before_body_end' => 'Page Bottom',
        'footer' => 'Footer'
    ];

    public $block_positions = [
        'after' => 'Bottom',
        'before' => 'Top',
    ];

    /**
     * GET getresponse/webform/index
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Subscription via a form'))->_title($this->__('GetResponse'));

        $this->settings->layout_positions = $this->layout_positions;
        $this->settings->block_positions = $this->block_positions;

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/viawebform.phtml')
            ->assign('forms', $this->api->getPublishedForms())
            ->assign('webforms', $this->api->getPublishedWebForms())
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

        if (!empty($params['gr_webform_type']) && $params['gr_webform_type'] == 'old') {
            $webforms = $this->api->getWebform($params['webform_id']);
        } else {
            $webforms = $this->api->getForm($params['webform_id']);
        }

        if (empty($webforms->codeDescription)) {
            $data = [
                'webformId' => $params['webform_id'],
                'activeSubscription' => $isEnabled,
                'layoutPosition' => $params['layout_position'],
                'blockPosition' => $params['block_position'],
                'webformTitle' => trim($params['webform_title']),
                'url' => $webforms->scriptUrl
            ];
            $webform = WebformFactory::createFromArray($data);
            $webformRepository->create($webform);

            $this->_getSession()->addSuccess('Form published');
        } else {
            $this->_getSession()->addError('Error - please try again');
        }

        $this->_redirect('*/*/index');
    }

}