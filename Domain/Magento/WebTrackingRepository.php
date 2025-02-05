<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use Magento\Framework\Stdlib\CookieManagerInterface;

class WebTrackingRepository
{
    public const VISITOR_UUID_COOKIE_NAME = 'gaVisitorUuid';

    private $cookieManager;

    public function __construct(CookieManagerInterface $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }

    public function findVisitor(): ?Visitor
    {
        $uuid = $this->cookieManager->getCookie(self::VISITOR_UUID_COOKIE_NAME);

        if (null === $uuid) {
            return null;
        }

        return new Visitor($uuid);
    }
}
