<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use GetResponse\GetResponseIntegration\Helper\Message;

class EcommerceSettings
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    private $status;
    private $shopId;
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

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getListId(): string
    {
        return $this->listId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param $status
     * @throws ValidationException
     */
    private function setStatus($status)
    {
        $message = 'Incorrect ecommerce settings status';
        if (!in_array($status, [self::STATUS_ENABLED, self::STATUS_DISABLED], true)) {
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
