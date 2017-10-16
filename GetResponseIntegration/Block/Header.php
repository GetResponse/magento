<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Header
 * @package GetResponse\GetResponseIntegration\Block
 */
class Header extends Template
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager, Repository $repository)
    {
        parent::__construct($context, $objectManager);
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getSnippetCode()
    {
        $data = $this->repository->getSnippetCode();

        if (isset($data['web_traffic']) && isset($data['tracking_code_snippet']) && 'disabled' !== $data['web_traffic']) {
            return $data['tracking_code_snippet'];
        }
    }
}
