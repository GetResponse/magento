<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Lists;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Getresponse\Repository as GrRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\Http;

/**
 * Class Create
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Rules
 */
class Create extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param GrRepository $grRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        GrRepository $grRepository)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->repository = $repository;
        $this->grRepository = $grRepository;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam('back_url');
        $backParam = $this->getRequest()->getParam('back');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::automation');
        $resultPage->getConfig()->getTitle()->prepend('New Contact List');

        /** @var Http $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();

        if (empty($data)) {
            return $resultPage;
        }

        // validator
        $error = $this->validateNewListParams($data);

        $resultRedirect = $this->resultRedirectFactory->create();

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
            $resultRedirect->setPath('getresponseintegration/lists/create/back/' . $backParam);
            return $resultRedirect;
        }

        $lang = substr($this->repository->getMagentoCountryCode(), 0, 2);

        $params = [];
        $params['name'] = $data['campaign_name'];
        $params['languageCode'] = (isset($lang)) ? $lang : 'EN';
        $params['confirmation'] = [
            'fromField' => ['fromFieldId' => $data['from_field']],
            'replyTo' => ['fromFieldId' => $data['reply_to_field']],
            'subscriptionConfirmationBodyId' => $data['confirmation_body'],
            'subscriptionConfirmationSubjectId' => $data['confirmation_subject']
        ];

        $result = $this->grRepository->createCampaign($params);

        if (isset($result->httpStatus) && (int)$result->httpStatus >= 400) {
            $resultRedirect->setPath($backUrl);
            $this->messageManager->addErrorMessage(isset($result->codeDescription) ? $result->codeDescription . ' - uuid: ' . $result->uuid : 'Something goes wrong');
            $resultRedirect->setPath('getresponseintegration/lists/create/back/' . $backParam);
            return $resultRedirect;
        } else {
            $this->messageManager->addSuccessMessage('List created');
            $resultRedirect->setPath($backUrl);
            return $resultRedirect;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    private function validateNewListParams($data)
    {
        if (strlen($data['campaign_name']) < 3) {
            return 'You need to enter a name that\'s at least 3 characters long';
        }

        if (strlen($data['from_field']) === 0) {
            return 'You need to select a sender email address';
        }

        if (strlen($data['reply_to_field']) === 0) {
            return 'Reply-To is a required field';
        }

        if (strlen($data['confirmation_subject']) === 0) {
            return 'Confirmation subject is a required field';
        }

        if (strlen($data['confirmation_body']) === 0) {
            return 'Confirmation body is a required field';
        }
    }
}
