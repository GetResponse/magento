<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class FacebookPixel
{
    private $isActive;
    private $codeSnippet;

    public function __construct(
        bool $isActive = false,
        string $codeSnippet = ''
    ) {
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
            'isEnabled' => (int) $this->isActive,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    public static function createFromRepository(array $data): FacebookPixel
    {
        if (empty($data)) {
            return new FacebookPixel(
                false,
                ''
            );
        }

        return new FacebookPixel((bool)$data['isEnabled'], $data['codeSnippet']);
    }

    public static function createFromRequest(array $data): FacebookPixel
    {
        if (!isset($data['facebookPixel'])) {
            throw new RuntimeException('incorrect FacebookPixel params');
        }

        return new FacebookPixel($data['facebookPixel']['isActive'], $data['facebookPixel']['codeSnippet']);
    }
}
