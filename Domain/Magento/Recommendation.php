<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class Recommendation implements SnippetInterface
{
    private $isActive;
    private $snippet;

    public function __construct(bool $isActive = false, string $snippet = '')
    {
        $this->isActive = $isActive;
        $this->snippet = $snippet;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCodeSnippet(): string
    {
        return $this->snippet;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int) $this->isActive,
            'codeSnippet' => $this->snippet
        ];
    }

    public static function createFromRepository(array $data): self
    {
        if (empty($data)) {
            return new self(false, '');
        }

        return new self((bool)$data['isEnabled'], $data['codeSnippet']);
    }

    public static function createFromRequest(array $data): self
    {
        return new self($data['recommendation']['is_active'] ?? false, $data['recommendation']['snippet'] ?? "");
    }
}
