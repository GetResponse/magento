<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

use RuntimeException;

class WebForm
{
    private $isEnabled;
    private $url;
    private $webFormId;
    private $sidebar;

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

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getWebFormId(): string
    {
        return $this->webFormId;
    }

    public function getSidebar(): string
    {
        return $this->sidebar;
    }

    public function toArray(): array
    {
        return [
            'isEnabled' => (int)$this->isEnabled,
            'url' => $this->url,
            'webformId' => $this->webFormId,
            'sidebar' => $this->sidebar
        ];
    }

    public static function createFromRepository(array $data): WebForm
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

    public static function createFromRequest(array $data): WebForm
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

    public static function createFromArray(array $data): WebForm
    {
        return new WebForm(
            (bool) $data['isEnabled'],
            $data['url'],
            $data['webFormId'],
            $data['place']
        );
    }
}
