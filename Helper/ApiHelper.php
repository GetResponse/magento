<?php
namespace GetResponse\GetResponseIntegration\Helper;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository;

/**
 * Class ApiHelper
 * @package GetResponse\GetResponseIntegration\Helper
 */
class ApiHelper
{
    /** @var Repository */
    public $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $user_customs
     * @return array
     */
    public function setCustoms($user_customs)
    {
        $custom_fields = [];

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {
            $custom = $this->repository->getCustomFieldByName($name);

            if (!isset($custom['customFieldId'])) {
                $custom = $this->repository->addCustomField([
                    'name' => $name,
                    'type' => "text",
                    'hidden' => "false",
                    'values' => [$value],
                ]);

                if (empty($custom)) {
                    continue;
                }
            }

            $custom_fields[] = [
                'customFieldId' => $custom['customFieldId'],
                'value' => [$value]
            ];
        }

        return $custom_fields;
    }

    /**
     * Merge user custom fields selected on WP admin site with those from gr account
     * @param $results
     * @param $user_customs
     *
     * @return array
     */
    public function mergeUserCustoms($results, $user_customs)
    {
        $custom_fields = [];

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($user_customs))) {
                    $value = [$user_customs[$customs->name]];
                    unset($user_customs[$customs->name]);
                }

                $custom_fields[] = [
                    'customFieldId' => $customs->customFieldId,
                    'value' => $value
                ];
            }
        }

        return array_merge($custom_fields, $this->setCustoms($user_customs));
    }
}
