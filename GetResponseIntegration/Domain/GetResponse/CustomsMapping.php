<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CustomsMapping
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomsMapping
{
    /** @var int */
    private $id;

    /** @var string */
    private $shopId;

    /** @var int */
    private $customId;

    /** @var string */
    private $getresponseCustomId;

    /**
     * @param int $id
     * @param string $shopId
     * @param int $customId
     * @param string $getresponseCustomId
     */
    public function __construct($id, $shopId, $customId, $getresponseCustomId)
    {
        $this->id = $id;
        $this->shopId = $shopId;
        $this->customId = $customId;
        $this->getresponseCustomId = $getresponseCustomId;
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
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @return int
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @return string
     */
    public function getGetresponseCustomId()
    {
        return $this->getresponseCustomId;
    }
}
