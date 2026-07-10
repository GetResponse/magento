<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class FacebookBusinessExtension implements SnippetInterface
{
    /** @var bool */
    private $isActive;
    /** @var string */
    private $codeSnippet;

    /**
     * @param bool $isActive
     * @param string $codeSnippet
     */
    public function __construct(
        bool $isActive = false,
        string $codeSnippet = ''
    ) {
        $this->isActive = $isActive;
        $this->codeSnippet = $codeSnippet;
    }

    /**
     * Check active.
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get code snippet.
     */
    public function getCodeSnippet(): string
    {
        return $this->codeSnippet;
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => (int) $this->isActive,
            'codeSnippet' => $this->codeSnippet
        ];
    }

    /**
     * Create from repository.
     *
     * @param array $data
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRepository(array $data): self
    {
        if (empty($data)) {
            return new self(
                false,
                ''
            );
        }

        return new self((bool)$data['isEnabled'], $data['codeSnippet']);
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
        if (!isset($data['facebook_business_extension'])) {
            throw RequestValidationException::create('Incorrect Facebook Business Extension params');
        }

        return new self(
            $data['facebook_business_extension']['is_active'],
            $data['facebook_business_extension']['snippet']
        );
    }
}
