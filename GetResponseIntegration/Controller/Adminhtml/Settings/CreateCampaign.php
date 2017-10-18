<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AccessValidator;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Getresponse\Repository as GrRepository;
use GetResponse\GetResponseIntegration\Helper\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class CreateCampaign
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class CreateCampaign extends Action
{
    /** @var PageFactory */
    private $resultPageFactory;

    /** @var Http  */
    private $request;

    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param JsonFactory $resultJsonFactory
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        JsonFactory $resultJsonFactory,
        AccessValidator $accessValidator
    )
    {
        parent::__construct($context);

        if (false === $accessValidator->isConnectedToGetResponse()) {
            $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $this->resultPageFactory = $resultPageFactory;
        $this->request = $this->getRequest();
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->buildRepository();
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $data = $this->request->getPostValue();
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

        return $this->resultJsonFactory->create()->setData(
            $this->grRepository->createCampaign($params)
        );
    }
}
