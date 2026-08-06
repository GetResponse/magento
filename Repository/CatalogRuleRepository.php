<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Repository;

use GetResponse\GetResponseIntegration\Api\CatalogRule as CatalogRuleDto;
use GetResponse\GetResponseIntegration\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CatalogRuleRepository implements CatalogRuleRepositoryInterface
{
    /** @var ResourceConnection */
    private $resourceConnection;
    /** @var DateTime */
    private $dateTime;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
    }

    /**
     * Get list of catalog rules.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CatalogRuleDto[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $pageSize = $searchCriteria->getPageSize() ?: 100;
        $currentPage = $searchCriteria->getCurrentPage() ?: 1;

        $pageSize = ($pageSize > 0) ? $pageSize : 100;
        $currentPage = ($currentPage > 0) ? $currentPage : 1;

        $connection = $this->resourceConnection->getConnection();
        $today = $this->dateTime->gmtDate('Y-m-d');

        $catalogRulePriceTable = $this->resourceConnection->getTableName('catalogrule_product_price');
        $catalogRuleTable = $this->resourceConnection->getTableName('catalogrule');
        $catalogRuleProductTable = $this->resourceConnection->getTableName('catalogrule_product');

        $ruleSelect = $connection->select()
            ->from(['rule_table' => $catalogRuleTable], [
                'rule_id',
                'name' => 'rule_table.name',
                'description' => 'rule_table.description',
                'from_date' => 'rule_table.from_date',
                'to_date' => 'rule_table.to_date',
                'is_active' => 'rule_table.is_active',
                'conditions_serialized' => 'rule_table.conditions_serialized',
                'actions_serialized' => 'rule_table.actions_serialized',
                'stop_rules_processing' => 'rule_table.stop_rules_processing',
                'sort_order' => 'rule_table.sort_order',
                'simple_action' => 'rule_table.simple_action',
                'discount_amount' => 'rule_table.discount_amount',
                'website_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT rp.website_id)'),
                'customer_group_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT rp.customer_group_id)'),
                'product_ids' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT price_table.product_id)')
            ])
            ->joinInner(
                ['rp' => $catalogRuleProductTable],
                'rp.rule_id = rule_table.rule_id',
                []
            )
            ->joinInner(
                ['price_table' => $catalogRulePriceTable],
                'price_table.product_id = rp.product_id ' .
                'AND price_table.website_id = rp.website_id ' .
                'AND price_table.customer_group_id = rp.customer_group_id',
                []
            )
            ->where('price_table.rule_date = ?', $today)
            ->where('rule_table.is_active = ?', 1);

        $this->applyFilters($searchCriteria, $ruleSelect);

        $ruleSelect->group('rule_table.rule_id');

        $ruleSelect->order('rule_table.rule_id ASC')
            ->limitPage($currentPage, $pageSize);

        $ruleData = $connection->fetchAll($ruleSelect);
        $results = [];

        foreach ($ruleData as $row) {
            $results[] = new CatalogRuleDto(
                (int)$row['rule_id'],
                $row['name'] ?? null,
                $row['description'] ?? null,
                $row['from_date'] ?? null,
                $row['to_date'] ?? null,
                (bool)$row['is_active'],
                $row['conditions_serialized'] ?? null,
                $row['actions_serialized'] ?? null,
                (bool)$row['stop_rules_processing'],
                (int)$row['sort_order'],
                (string)$row['simple_action'],
                (float)$row['discount_amount'],
                $this->explodeAndCastToInt($row['website_ids']),
                $this->explodeAndCastToInt($row['customer_group_ids']),
                $this->explodeAndCastToInt($row['product_ids'])
            );
        }

        return $results;
    }

    /**
     * Apply filters.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param \Magento\Framework\DB\Select $select
     */
    private function applyFilters(SearchCriteriaInterface $searchCriteria, $select)
    {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $groupConditions = [];
            foreach ($group->getFilters() as $filter) {
                $field = $this->mapField($filter->getField());
                if (!$field) {
                    continue;
                }
                $groupConditions[] = $this->buildCondition(
                    $field,
                    $filter->getValue(),
                    $filter->getConditionType() ?: 'eq'
                );
            }
            if (!empty($groupConditions)) {
                $select->where('(' . implode(') OR (', $groupConditions) . ')');
            }
        }
    }

    /**
     * Map field.
     *
     * @param string $field
     * @return string|null
     */
    private function mapField($field)
    {
        $mapping = [
            'rule_id' => 'rule_table.rule_id',
            'website_id' => 'rp.website_id',
            'product_id' => 'price_table.product_id',
            'customer_group_id' => 'rp.customer_group_id'
        ];
        return $mapping[$field] ?? null;
    }

    /**
     * Build condition.
     *
     * @param string $field
     * @param mixed $value
     * @param string $conditionType
     * @return string
     */
    private function buildCondition($field, $value, $conditionType)
    {
        $connection = $this->resourceConnection->getConnection();
        switch ($conditionType) {
            case 'in':
            case 'nin':
                $values = is_array($value) ? $value : explode(',', (string)$value);
                $operator = ($conditionType === 'in') ? 'IN' : 'NOT IN';
                return $connection->quoteInto("$field $operator (?)", $values);
            case 'gt':
                return $connection->quoteInto("$field > ?", $value);
            case 'lt':
                return $connection->quoteInto("$field < ?", $value);
            case 'gteq':
                return $connection->quoteInto("$field >= ?", $value);
            case 'lteq':
                return $connection->quoteInto("$field <= ?", $value);
            case 'neq':
                return $connection->quoteInto("$field != ?", $value);
            case 'eq':
            default:
                return $connection->quoteInto("$field = ?", $value);
        }
    }

    /**
     * Explode and cast to int.
     *
     * @param string|null $value
     * @return int[]
     */
    private function explodeAndCastToInt(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        return array_map('intval', explode(',', $value));
    }
}
