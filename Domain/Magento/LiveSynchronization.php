<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class LiveSynchronization
{
    public const TYPE_CONTACT = 'contact';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_ECOMMERCE = 'ecommerce';

    private $isActive;
    private $callbackUrl;
    private $type;

    public function __construct(bool $isActive, string $callbackUrl, string $type)
    {
        $this->isActive = $isActive;
        $this->callbackUrl = $callbackUrl;
        $this->type = $type;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isContactSynchronization(): bool
    {
        return $this->type === self::TYPE_CONTACT;
    }

    public function isProductSynchronization(): bool
    {
        return $this->type === self::TYPE_PRODUCT;
    }

    public function isEcommerceSynchronization(): bool
    {
        return $this->type === self::TYPE_ECOMMERCE;
    }

    public function shouldImportCart(): bool
    {
        return $this->isActive() && $this->isEcommerceSynchronization();
    }

    public function shouldImportOrder(): bool
    {
        return $this->isActive() && $this->isEcommerceSynchronization();
    }

    public function shouldImportProduct(): bool
    {
        return $this->isActive() && ($this->isEcommerceSynchronization() || $this->isProductSynchronization());
    }

    public function shouldImportContact(): bool
    {
        return $this->isActive() && $this->isContactSynchronization();
    }

    /**
     * @throws RequestValidationException
     * @return static
     * @param array $data
     */
    public static function createFromRequest(array $data): self
    {
        if (!isset(
            $data['liveSynchronization']['isActive'],
            $data['liveSynchronization']['callbackUrl'],
            $data['liveSynchronization']['type']
        )) {
            throw RequestValidationException::create('Incorrect LiveSynchronization params');
        }

        if (true === $data['liveSynchronization']['isActive'] && !in_array(
                $data['liveSynchronization']['type'],
                [self::TYPE_CONTACT, self::TYPE_PRODUCT, self::TYPE_ECOMMERCE],
                true
            )) {
            throw new RequestValidationException('Invalid live synchronization type');
        }

        return new self(
            $data['liveSynchronization']['isActive'],
            $data['liveSynchronization']['callbackUrl'],
            $data['liveSynchronization']['type']
        );
    }

    public static function createFromRepository($data): self
    {
        $isActive = !empty($data) ? (bool)$data['isActive'] : false;
        $callbackUrl = !empty($data) ? $data['callbackUrl'] : '';
        $type = !empty($data) ? $data['type'] : '';

        return new self($isActive, $callbackUrl, $type);
    }

    public function toArray(): array
    {
        return [
            'isActive' => $this->isActive,
            'callbackUrl' => $this->callbackUrl,
            'type' => $this->type
        ];
    }
}
