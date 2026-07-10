<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebForm
{
    /** @var bool */
    private $isEnabled;
    /** @var string */
    private $url;
    /** @var string */
    private $webFormId;
    /** @var string */
    private $sidebar;

    /**
     * @param bool $isEnabled
     * @param string $url
     * @param string $webformId
     * @param string $sidebar
     */
    public function __construct(
        bool $isEnabled,
        string $url,
        string $webformId,
        string $sidebar
    ) {
        $this->isEnabled = $isEnabled;
        $this->url = $url;
        $this->webFormId = $webformId;
        $this->sidebar = $sidebar;
    }

    /**
     * Check enabled.
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Get url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get web form id.
     */
    public function getWebFormId(): string
    {
        return $this->webFormId;
    }

    /**
     * Get sidebar.
     */
    public function getSidebar(): string
    {
        return $this->sidebar;
    }

    /**
     * Handle to array.
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'url' => $this->url,
            'webformId' => $this->webFormId,
            'sidebar' => $this->sidebar
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
            return new WebForm(false, '', '', '');
        }
        return new WebForm(
            isset($data['isEnabled']) && (bool)$data['isEnabled'],
            $data['url'],
            $data['webformId'],
            $data['sidebar']
        );
    }

    /**
     * Create from request.
     *
     * @param array $data
     */
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Magento2.Annotation.MethodArguments.NoCommentBlock
    public static function createFromRequest(array $data): self
    {
        if (!isset($data['web_form'])) {
            throw new RuntimeException('incorrect WebForm params');
        }

        return new WebForm(
            isset($data['web_form']['is_active']) && (bool)$data['web_form']['is_active'],
            $data['web_form']['url'],
            $data['web_form']['form_id'],
            $data['web_form']['block']
        );
    }
}
