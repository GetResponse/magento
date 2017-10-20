<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class WebformCollectionFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class WebformCollectionFactory
{
    /**
     * @param array $forms
     * @param array $webForms
     *
     * @return WebformsCollection
     */
    public static function buildFromApiResponse(array $forms, array $webForms)
    {
        $collection = new WebformsCollection();

        if (empty($forms) && empty($webForms)) {
            return $collection;
        }

        if (count($forms) > 0) {
            foreach ($forms as $row) {
                $collection->add(new Webform(
                   $row->formId,
                   $row->name,
                   $row->scriptUrl
                ));
            }
        }

        if (count($webForms) > 0) {
            foreach ($webForms as $row) {
                $collection->add(new Webform(
                    $row->webformId,
                    $row->name,
                    $row->scriptUrl
                ));
            }
        }

        return $collection;
    }
}
