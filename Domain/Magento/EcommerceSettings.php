<?php
namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class EcommerceSettings
 * @package GetResponse\GetResponseIntegration\Domain\Magento
 */
class EcommerceSettings
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    /** @var string */
    private $status;

    /** @var string */
    private $shopId;

    /** @var string */
    private $listId;

    /**
     * @param string $status
     * @param string $shopId
     * @param string $listId
     * @throws ValidationException
     */
    public function __construct($status, $shopId, $listId)
    {
        $this->setStatus($status);
        $this->setShopId($shopId);
        $this->setListId($listId);
    }

    /**
     * @return string
     */
    public function isEnabled()
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @throws ValidationException
     */
    private function setStatus($status)
    {
        $message = 'Incorrect ecommerce settings status';
        if (!in_array($status, [self::STATUS_ENABLED, self::STATUS_DISABLED])) {
            throw ValidationException::createForInvalidValue($message);
        }
        $this->status = $status;
    }

    /**
     * @param string $shopId
     * @throws ValidationException
     */
    private function setShopId($shopId)
    {
        if ($this->status === self::STATUS_ENABLED && empty($shopId)) {
            throw ValidationException::createForInvalidValue(Message::STORE_CHOOSE);
        }
        $this->shopId = $shopId;
    }

    /**
     * @param string $listId
     * @throws ValidationException
     */
    private function setListId($listId)
    {
        if ($this->status === self::STATUS_ENABLED && empty($listId)) {
            throw ValidationException::createForInvalidValue(Message::SELECT_CONTACT_LIST);
        }
        $this->listId = $listId;
    }
}
