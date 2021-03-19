<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Newsletter;

use GetResponse\GetResponseIntegration\Controller\Adminhtml\AbstractController;
use GetResponse\GetResponseIntegration\Helper\PageTitle;
use GetResponse\GetResponseIntegration\Helper\Route;

class Index extends AbstractController
{
    public function execute()
    {
        parent::execute();

        if ($this->shouldRedirectToStore()) {
            return $this->redirectToStore(Route::NEWSLETTER_INDEX_ROUTE);
        }

        return $this->render(PageTitle::NEWSLETTER);
    }
}
