<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class FacebookPixel implements SnippetInterface
{
    /**
     * Stored value for is active.
     *
     * @var bool
     */
    private $isActive;
    /**
     * Stored value for code snippet.
     *
     * @var string
     */
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
     * @return self
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRepository(array $data): self
    {
        if (empty($data)) {
            return new FacebookPixel(
                false,
                ''
            );
        }

        return new FacebookPixel((bool)$data['isEnabled'], $data['codeSnippet']);
    }

    /**
     * Create from request.
     *
     * @param array $data
     * @throws RequestValidationException
     * @return FacebookPixel
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRequest(array $data): self
    {
        if (!isset($data['facebook_pixel'])) {
            throw RequestValidationException::create('Incorrect FacebookPixel params');
        }

        return new FacebookPixel($data['facebook_pixel']['is_active'], $data['facebook_pixel']['snippet']);
    }
}
