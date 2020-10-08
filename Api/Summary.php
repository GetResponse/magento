<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\ModuleList;

/**
 * @api
 */
class Summary
{
    const MODULE_NAME = 'GetResponse_GetResponseIntegration';

    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * @return array
     */
    public function getPluginSummary()
    {
        $versionInfo = $this->objectManager->get(ModuleList::class)->getOne(self::MODULE_NAME);
        $pluginVersion = isset($versionInfo['setup_version']) ? $versionInfo['setup_version'] : '';

        return [
            [
                'general' => [
                    'plugin_version' => $pluginVersion
                ],
                'sections' => [
                    'registration' => [
                        'enabled' => true,
                        'list_id' => 'x393',
                        'autoresponder_enabled' => false,
                        'autoresponder_cycle' => null
                    ],
                    'webforms' => [
                        'enabled' => true,
                        'form_id' => '49s3',
                        'block_id' => 'footer'
                    ],
                    'newsletter' => [
                        'enabled' => true,
                        'list_id' => 'x939',
                        'autoresponder_enabled' => false,
                        'autoresponder_cycle' => null
                    ],
                    'web_event_tracking' => [
                        'enabled' => true
                    ],
                    'ecommerce' => [
                        'enabled' => true,
                        'list_id' => 'x939',
                        'store_id' => 'x939'
                    ],
                ]

            ]
        ];
    }
}
