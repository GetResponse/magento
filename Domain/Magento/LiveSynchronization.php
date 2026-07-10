<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class LiveSynchronization
{
    public const TYPE_CONTACT = 'Contacts';
    public const TYPE_PRODUCT = 'Products';
    public const TYPE_ECOMMERCE = 'FullEcommerce';

    /** @var bool */
    private $isActive;
    /** @var string */
    private $callbackUrl;
    /** @var string */
    private $type;

    /**
     * @param bool $isActive
     * @param string $callbackUrl
     * @param string $type
     */
    public function __construct(bool $isActive, string $callbackUrl, string $type)
    {
        $this->isActive = $isActive;
        $this->callbackUrl = $callbackUrl;
        $this->type = $type;
    }

    /**
     * Check active.
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get callback url.
     */
    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * Get type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check contact synchronization.
     */
    public function isContactSynchronization(): bool
    {
        return $this->type === self::TYPE_CONTACT;
    }

    /**
     * Check product synchronization.
     */
    public function isProductSynchronization(): bool
    {
        return $this->type === self::TYPE_PRODUCT;
    }

    /**
     * Check ecommerce synchronization.
     */
    public function isEcommerceSynchronization(): bool
    {
        return $this->type === self::TYPE_ECOMMERCE;
    }

    /**
     * Handle should import cart.
     */
    public function shouldImportCart(): bool
    {
        return $this->isActive() && $this->isEcommerceSynchronization();
    }

    /**
     * Handle should import order.
     */
    public function shouldImportOrder(): bool
    {
        return $this->isActive() && $this->isEcommerceSynchronization();
    }

    /**
     * Handle should import product.
     */
    public function shouldImportProduct(): bool
    {
        return $this->isActive() && ($this->isEcommerceSynchronization() || $this->isProductSynchronization());
    }

    /**
     * Handle should import customer.
     */
    public function shouldImportCustomer(): bool
    {
        return $this->isActive() && ($this->isEcommerceSynchronization() || $this->isContactSynchronization());
    }

    /**
     * Create from request.
     *
     * @param array $data
     * @throws RequestValidationException
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRequest(array $data): self
    {
        if (!isset(
            $data['live_synchronization']['is_active'],
            $data['live_synchronization']['url'],
            $data['live_synchronization']['type']
        )) {
            throw RequestValidationException::create('Incorrect LiveSynchronization params');
        }

        if (true === $data['live_synchronization']['is_active'] && !in_array(
            $data['live_synchronization']['type'],
            [self::TYPE_CONTACT, self::TYPE_PRODUCT, self::TYPE_ECOMMERCE],
            true
        )) {
            throw new RequestValidationException('Invalid live synchronization type');
        }

        return new self(
            $data['live_synchronization']['is_active'],
            $data['live_synchronization']['url'],
            $data['live_synchronization']['type']
        );
    }

    /**
     * Create from repository.
     *
     * @param mixed $data
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRepository($data): self
    {
        $isEnabled = !empty($data) ? (bool)$data['isEnabled'] : false;
        $callbackUrl = !empty($data) ? $data['callbackUrl'] : '';
        $type = !empty($data) ? $data['type'] : '';

        return new self($isEnabled, $callbackUrl, $type);
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => (int) $this->isActive,
            'callbackUrl' => $this->callbackUrl,
            'type' => $this->type
        ];
    }
}
