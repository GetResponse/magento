<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

class BlockPosition
{
    public const CONTENT = 'content';
    public const SIDEBAR_ADDITIONAL = 'div.sidebar.additional';
    public const SIDEBAR_MAIN = 'sidebar.main';
    public const FOOTER = 'footer-container';
    public const BOTTOM = 'page.bottom';

    public const POSITIONS = [
        self::CONTENT => 'Content Top',
        self::BOTTOM => 'Content Bottom',
        self::FOOTER => 'Footer',
        self::SIDEBAR_MAIN => 'Sidebar Top',
        self::SIDEBAR_ADDITIONAL => 'Sidebar Bottom'
    ];
}
