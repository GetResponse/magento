<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class WebformCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class WebformsCollection
{
    /** @var array|Webform[] */
   private $webforms;

    /**
     * * @param Webform $webform
     *
     */
    public function add(Webform $webform)
    {
        $this->webforms[] = $webform;
    }

    /**
     * @return array|Webform[]
     */
    public function getWebforms()
    {
        return $this->webforms;
    }
}
