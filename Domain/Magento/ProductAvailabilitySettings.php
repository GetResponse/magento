<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class ProductAvailabilitySettings
{
    /** @var bool */
    private $isEnabled;

    /**
     * @param bool $isEnabled
     */
    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * Whether availability processing is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Create from a request containing the product_availability section.
     *
     * @param array $data
     * @return self
     * @throws RequestValidationException
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRequest(array $data): self
    {
        $isEnabled = $data['product_availability']['is_enabled'] ?? null;

        if (!is_bool($isEnabled)) {
            throw RequestValidationException::create('Product availability is_enabled must be a boolean.');
        }

        return new self($isEnabled);
    }

    /**
     * Create from stored settings. Anything other than an explicit true keeps availability disabled.
     *
     * @param array $data
     * @return self
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRepository(array $data): self
    {
        return new self(($data['is_enabled'] ?? false) === true);
    }

    /**
     * Serialize settings for storage.
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['is_enabled' => $this->isEnabled];
    }
}
