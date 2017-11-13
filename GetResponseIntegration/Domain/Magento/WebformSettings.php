<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebformSettings
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class WebformSettings
{
    /** @var bool */
    private $isEnabled;

    /** @var string */
    private $url;

    /** @var string */
    private $webformId;

    /** @var string */
    private $sidebar;

    /**
     * @param bool $isEnabled
     * @param string $url
     * @param string $webformId
     * @param string $sidebar
     */
    public function __construct($isEnabled, $url, $webformId, $sidebar)
    {
        $this->isEnabled = (bool)$isEnabled;
        $this->url = $url;
        $this->webformId = $webformId;
        $this->sidebar = $sidebar;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getWebformId()
    {
        return $this->webformId;
    }

    /**
     * @return string
     */
    public function getSidebar()
    {
        return $this->sidebar;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'url' => $this->url,
            'webformId' => $this->webformId,
            'sidebar' => $this->sidebar
        ];
    }
}
