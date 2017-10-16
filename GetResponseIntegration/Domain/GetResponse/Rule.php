<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\Magento\Category;

/**
 * Class Rule
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Rule
{
    /** @var Category */
    private $category;

    /** @var string */
    private $action;

    /** @var Campaign */
    private $campaign;

    /** @var Autoresponder */
    private $autoresponder;

    /**
     * @param Category $category
     * @param string $action
     * @param Campaign $campaign
     * @param Autoresponder $autoresponder
     */
    public function __construct(Category $category, $action, Campaign $campaign, Autoresponder $autoresponder)
    {
        $this->category = $category;
        $this->action = $action;
        $this->campaign = $campaign;
        $this->autoresponder = $autoresponder;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return Autoresponder
     */
    public function getAutoresponder()
    {
        return $this->autoresponder;
    }
}
