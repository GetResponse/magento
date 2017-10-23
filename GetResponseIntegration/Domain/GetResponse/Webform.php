<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Webform
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /**
     * @param string $id
     * @param string $name
     * @param string $url
     */
    public function __construct($id, $name, $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
