<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api\Data;

/**
 * Newsletter subscriber.
 *
 * @api
 */
interface NewsletterSubscriberInterface
{
    public const ID = 'id';
    public const EMAIL = 'email';

    /**
     * Get subscriber ID.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Set subscriber ID.
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): NewsletterSubscriberInterface;

    /**
     * Get subscriber email.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Set subscriber email.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): NewsletterSubscriberInterface;
}
