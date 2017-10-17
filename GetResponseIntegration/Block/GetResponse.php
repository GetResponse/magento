<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class GetResponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class GetResponse extends Template
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context);
        $this->_objectManager = $objectManager;
    }

    /**
     * @return bool|int
     */
    public function checkApiKey()
    {
        return true;
        $repository = (new RepositoryFactory($this->_objectManager))->buildRepository();
        $response = $repository->getAccountDetails();

        if (isset($response->accountId)) {
            return true;
        } else {
            return false;
        }
    }
}
