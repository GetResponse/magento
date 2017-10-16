<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Autoresponder
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Autoresponder
{
    /** @var string */
    private $id;

    /** @var int */
    private $cycleDay;

    /** @var string */
    private $title;

    /**
     * @param string $id
     * @param int $cycleDay
     * @param string $title
     */
    public function __construct($id, $cycleDay, $title)
    {
        $this->id = $id;
        $this->cycleDay = $cycleDay;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCycleDay()
    {
        return $this->cycleDay;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
