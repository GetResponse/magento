<?php
namespace GetResponse\GetResponseIntegration\Ui\Component\Listing\Column\Getresponsecustomsmappinggrid;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {

            foreach ($dataSource["data"]["items"] as & $item) {

                $name = $this->getData("name");
                $id = "X";
                if(isset($item["id"]))
                {
                    $id = $item["id"];
                }

                if ($item['default'] == '1') {
                    continue;
                }

                $item[$name]["view"] = [
                    "href"=>$this->getContext()->getUrl(
                        "getresponseintegration/rules/create",["id"=>$id]),
                    "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
