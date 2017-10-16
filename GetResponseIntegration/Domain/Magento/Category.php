<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

/**
 * Class Category
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class Category
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
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
}
