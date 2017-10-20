<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CustomsCollectionFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldsCollectionFactory
{
    /**
     * @param array $data
     *
     * @return CustomFieldsCollection
     */
    public static function buildFromRepository(array $data)
    {
        $collection = new CustomFieldsCollection();

        if (empty($data)) {
            return $collection;
        }

        foreach ($data as $row) {
            $collection->add(new CustomField(
                $row->id,
                $row->customField,
                $row->customValue,
                $row->customName,
                $row->isDefault,
                $row->isActive
            ));
        }

        return $collection;
    }

    /**
     * @param array $customs
     * @param array $allCustoms
     *
     * @return CustomFieldsCollection
     */
    public static function buildFromUserPayload(array $customs, array $allCustoms)
    {
        $collection = new CustomFieldsCollection();

        if (empty($allCustoms)) {
            return $collection;
        }

        foreach ($allCustoms as $custom) {
            if (1 === $custom->isDefault) {
                $collection->add(new CustomField(
                    $custom->id,
                    $custom->customField,
                    $custom->customValue,
                    $custom->customName,
                    $custom->isDefault,
                    $custom->isActive
                ));
                continue;
            }

            if (isset($customs[$custom->customField])) {
                $custom->customName = $customs[$custom->customField];
                $custom->isActive = 1;
            } else {
                $custom->isActive = 0;
            }

            $collection->add(new CustomField(
                $custom->id,
                $custom->customField,
                $custom->customValue,
                $custom->customName,
                $custom->isDefault,
                $custom->isActive
            ));
        }

        return $collection;
    }
}
