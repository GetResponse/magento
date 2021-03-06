<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class FacebookAdsPixel implements SnippetInterface
{
    private $isActive;
    private $codeSnippet;

    public function __construct(bool $isActive = false, string $codeSnippet = '')
    {
        $this->isActive = $isActive;
        $this->codeSnippet = $codeSnippet;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCodeSnippet(): string
    {
        return $this->codeSnippet;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isActive,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    public static function createFromRepository(array $data): self
    {
        if (empty($data)) {
            return new self(false, '');
        }

        return new self((bool)$data['isEnabled'], $data['codeSnippet']);
    }

    /**
     * @throws RequestValidationException
     */
    public static function createFromRequest(array $data): FacebookAdsPixel
    {
        if (!isset($data['facebook_ads_pixel'])) {
            throw RequestValidationException::create('Incorrect FacebookAdsPixel params');
        }

        return new self($data['facebook_ads_pixel']['is_active'], $data['facebook_ads_pixel']['snippet']);
    }
}
