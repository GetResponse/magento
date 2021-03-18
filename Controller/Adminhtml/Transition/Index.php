<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Transition;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\PageTitle;

class Index extends AbstractController
{
    public function execute()
    {
        return $this->render(PageTitle::TRANSITION);
    }
}