<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Transition extends Template
{
    public function __construct(
        Context $context,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context);
        $this->magentoStore = $magentoStore;
    }
}
