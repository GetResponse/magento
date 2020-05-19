<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\Autoresponder;
use GrShareCode\ContactList\ContactListService;

trait AutoresponderTrait
{
    /**
     * @return string
     * @throws GetresponseApiException
     */
    public function getSerializedAutoresponders(): string
    {
        $result = [];

        $service = new ContactListService($this->apiClient);
        $responders = $service->getAutoresponders();

        /** @var Autoresponder $responder */
        foreach ($responders as $responder) {
            $result[$responder->getCampaignId()][$responder->getId()] = [
                'name' => $responder->getName(),
                'subject' => $responder->getSubject(),
                'dayOfCycle' => $responder->getCycleDay()
            ];
        }

        return $this->escapeHtml($this->serializer->serialize($result));
    }
}
