<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

class BlockPosition
{
    const CONTENT = 'content';
    const SIDEBAR_ADDITIONAL = 'div.sidebar.additional';
    const SIDEBAR_MAIN = 'sidebar.main';
    const FOOTER = 'footer-container';
    const BOTTOM = 'page.bottom';

    const POSITIONS = [
        self::CONTENT => 'Content Top',
        self::BOTTOM => 'Content Bottom',
        self::FOOTER => 'Footer',
        self::SIDEBAR_MAIN => 'Sidebar Top',
        self::SIDEBAR_ADDITIONAL => 'Sidebar Bottom'
    ];
}
