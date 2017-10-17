<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Header
 * @package GetResponse\GetResponseIntegration\Block
 */
class Header extends Template
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     */
    public function __construct(Context $context, Repository $repository)
    {
        parent::__construct($context);
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

        return '';
    }
}
