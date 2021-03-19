<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class LiveSynchronization
{
    private $isActive;
    private $callbackUrl;

    public function __construct(bool $isActive, string $callbackUrl)
    {
        $this->isActive = $isActive;
        $this->callbackUrl = $callbackUrl;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * @throws RequestValidationException
     * @return static
     * @param array $data
     */
    public static function createFromRequest(array $data): self
    {
        if (!isset($data['liveSynchronization']['isActive'], $data['liveSynchronization']['callbackUrl'])) {
            throw RequestValidationException::create('Incorrect LiveSynchronization params');
        }

        return new self($data['liveSynchronization']['isActive'], $data['liveSynchronization']['callbackUrl']);
    }

    public static function createFromRepository($data): self
    {
        $isActive = !empty($data) ? (bool) $data['isActive'] : false;
        $callbackUrl = !empty($data) ? $data['callbackUrl'] : '';

        return new self($isActive, $callbackUrl);
    }

    public function toArray(): array
    {
        return [
            'isActive' => $this->isActive,
            'callbackUrl' => $this->callbackUrl
        ];
    }
}
