<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\Data;

use GetResponse\GetResponseIntegration\Api\Data\NewsletterSubscriberInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class NewsletterSubscriber extends AbstractSimpleObject implements NewsletterSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return (int) $this->_get(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId(int $id): NewsletterSubscriberInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): string
    {
        return (string) $this->_get(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail(string $email): NewsletterSubscriberInterface
    {
        return $this->setData(self::EMAIL, $email);
    }
}
