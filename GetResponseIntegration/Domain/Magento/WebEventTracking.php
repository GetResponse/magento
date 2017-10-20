<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class WebEventTracking
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class WebEventTracking
{
    /** @var bool */
    private $isEnabled;

    /** @var bool */
    private $isFeatureTrackingEnabled;

    /** @var string */
    private $codeSnippet;

    /**
     * @param bool $isEnabled
     * @param bool $isFeatureTrackingEnabled
     * @param string $codeSnippet
     */
    public function __construct($isEnabled, $isFeatureTrackingEnabled, $codeSnippet)
    {
        $this->isEnabled = (bool) $isEnabled;
        $this->isFeatureTrackingEnabled = (bool) $isFeatureTrackingEnabled;
        $this->codeSnippet = $codeSnippet;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getCodeSnippet()
    {
        return $this->codeSnippet;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'isEnabled' => (int) $this->isEnabled,
            'isFeatureTrackingEnabled' => (int) $this->isFeatureTrackingEnabled,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    /**
     * @return bool
     */
    public function isFeatureTrackingEnabled()
    {
        return $this->isFeatureTrackingEnabled;
    }
}
