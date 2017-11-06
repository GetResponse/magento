<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Rule
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Rule
{
    /** @var int */
    private $id;

    /** @var int */
    private $categoryId;

    /** @var string */
    private $action;

    /** @var string */
    private $campaignId;

    /** @var int */
    private $autoresponderDay;

    /**
     * @param int $id
     * @param int $categoryId
     * @param string $action
     * @param string $campaignId
     * @param int $autoresponderDay
     */
    public function __construct($id, $categoryId, $action, $campaignId, $autoresponderDay)
    {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->action = $action;
        $this->campaignId = $campaignId;
        $this->autoresponderDay = $autoresponderDay;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->categoryId;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getCampaign()
    {
        return $this->campaignId;
    }

    /**
     * @return int
     */
    public function getAutoresponder()
    {
        return $this->autoresponderDay;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return [
            'id' => $this->id,
            'category' => $this->categoryId,
            'action' => $this->action,
            'campaign' => $this->campaignId,
            'cycle_day' => $this->autoresponderDay
        ];
    }
}
