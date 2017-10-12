<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Block\Export;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class CreateCampaign
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class CreateCampaign extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * CreateCampaign constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var Export $block */
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Export');
        $lang = substr($block->getStoreLanguage(), 0, 2);

        $params = [];
        $params['name'] = $data['campaign_name'];
        $params['languageCode'] = (isset($lang)) ? $lang : 'EN';
        $params['confirmation'] = [
            'fromField' => ['fromFieldId' => $data['from_field']],
            'replyTo' => ['fromFieldId' => $data['reply_to_field']],
            'subscriptionConfirmationBodyId' => $data['confirmation_body'],
            'subscriptionConfirmationSubjectId' => $data['confirmation_subject']
        ];

        echo json_encode($block->getClient()->createCampaign($params));
        die;
    }
}