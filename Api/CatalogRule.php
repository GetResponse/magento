<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class CatalogRule implements JsonSerializable
{
    /** @var int */
    private $ruleId;
    /** @var string */
    private $name;
    /** @var string */
    private $description;
    /** @var string */
    private $startDate;
    /** @var string */
    private $endDate;
    /** @var bool */
    private $isActive;
    /** @var string */
    private $conditionsSerialized;
    /** @var string */
    private $actionsSerialized;
    /** @var bool */
    private $stopRulesProcessing;
    /** @var int */
    private $sortOrder;
    /** @var string */
    private $simpleAction;
    /** @var float */
    private $discountAmount;
    /** @var int[] */
    private $websiteIds;
    /** @var int[] */
    private $customerGroupIds;
    /** @var int[] */
    private $productIds;

    /**
     * @param int $ruleId
     * @param string|null $name
     * @param string|null $description
     * @param string|null $startDate
     * @param string|null $endDate
     * @param bool $isActive
     * @param ?string $conditionsSerialized
     * @param ?string $actionsSerialized
     * @param bool $stopRulesProcessing
     * @param int $sortOrder
     * @param string $simpleAction
     * @param float $discountAmount
     * @param int[] $websiteIds
     * @param int[] $customerGroupIds
     * @param int[] $productIds
     */
    public function __construct(
        int $ruleId,
        ?string $name,
        ?string $description,
        ?string $startDate,
        ?string $endDate,
        bool $isActive,
        ?string $conditionsSerialized = null,
        ?string $actionsSerialized = null,
        bool $stopRulesProcessing = false,
        int $sortOrder = 0,
        string $simpleAction = '',
        float $discountAmount = 0.0,
        array $websiteIds = [],
        array $customerGroupIds = [],
        array $productIds = []
    ) {
        $this->ruleId = $ruleId;
        $this->name = (string)$name;
        $this->description = (string)$description;
        $this->startDate = (string)$startDate;
        $this->endDate = (string)$endDate;
        $this->isActive = $isActive;
        $this->conditionsSerialized = (string)$conditionsSerialized;
        $this->actionsSerialized = (string)$actionsSerialized;
        $this->stopRulesProcessing = $stopRulesProcessing;
        $this->sortOrder = $sortOrder;
        $this->simpleAction = $simpleAction;
        $this->discountAmount = $discountAmount;
        $this->websiteIds = $websiteIds;
        $this->customerGroupIds = $customerGroupIds;
        $this->productIds = $productIds;
    }

    /**
     * Get rule id.
     *
     * @return int
     */
    public function getRuleId(): int
    {
        return $this->ruleId;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get start date.
     *
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * Get end date.
     *
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * Get is active.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get conditions serialized.
     *
     * @return string
     */
    public function getConditionsSerialized(): string
    {
        return $this->conditionsSerialized;
    }

    /**
     * Get actions serialized.
     *
     * @return string
     */
    public function getActionsSerialized(): string
    {
        return $this->actionsSerialized;
    }

    /**
     * Get stop rules processing.
     *
     * @return bool
     */
    public function getStopRulesProcessing(): bool
    {
        return $this->stopRulesProcessing;
    }

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Get simple action.
     *
     * @return string
     */
    public function getSimpleAction(): string
    {
        return $this->simpleAction;
    }

    /**
     * Get discount amount.
     *
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    /**
     * Get website ids.
     *
     * @return array
     */
    public function getWebsiteIds(): array
    {
        return $this->websiteIds;
    }

    /**
     * Get customer group ids.
     *
     * @return array
     */
    public function getCustomerGroupIds(): array
    {
        return $this->customerGroupIds;
    }

    /**
     * Get product ids.
     *
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'rule_id' => $this->ruleId,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive,
            'conditions_serialized' => $this->conditionsSerialized,
            'actions_serialized' => $this->actionsSerialized,
            'stop_rules_processing' => $this->stopRulesProcessing,
            'sort_order' => $this->sortOrder,
            'simple_action' => $this->simpleAction,
            'discount_amount' => $this->discountAmount,
            'website_ids' => $this->websiteIds,
            'customer_group_ids' => $this->customerGroupIds,
            'product_ids' => $this->productIds
        ];
    }
}
