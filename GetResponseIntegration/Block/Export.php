<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Export
 * @package GetResponse\GetResponseIntegration\Block
 */
class Export extends GetResponse
{
    public $stats;

    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager, Repository $repository)
    {
        parent::__construct($context, $objectManager);
        $this->repository = $repository;
    }

    public function getCustomers()
    {
        return $this->repository->getCustomers();
    }

    /**
     * @return mixed
     */
    public function getActiveCustoms()
    {
        return $this->repository->getActiveCustoms();
    }

    /**
     * @return mixed
     */
    public function getDefaultCustoms()
    {
        return $this->repository->getDefaultCustoms();
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->getClient()->getCampaigns(['sort' => ['name' => 'asc']]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCampaign($id)
    {
        return $this->getClient()->getCampaign($id);
    }

    /**
     * @return mixed
     */
    public function getAccountFromFields()
    {
        return $this->getClient()->getAccountFromFields();
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsSubject($lang);
    }

    /**
     * @param $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang)
    {
        return $this->getClient()->getSubscriptionConfirmationsBody($lang);
    }

    /**
     * @return mixed
     */
    public function getAutomations()
    {
        return $this->repository->getAutomations();
    }

    /**
     * @param $category_id
     * @return mixed
     */
    public function getCategoryName($category_id)
    {
        return $this->repository->getCategoryName($category_id);
    }

    /**
     * @return mixed
     */
    public function getStoreCategories()
    {
        return $this->repository->getStoreCategories();
    }

    /**
     * @param $category \Magento\Catalog\Helper\Category|\Magento\Catalog\Model\Category
     */
    public function getSubcategories($category)
    {
        if ($category->hasChildren()) {
            $childrenCategories = $category->getChildren();
            foreach ($childrenCategories as $childrenCategory) {
                $string = '';
                for ($i = $childrenCategory->getLevel(); $i > 2; $i--) {
                    $string .= '-';
                }
                echo '<option value="' . $childrenCategory->getEntityId() . '"> ' .
                    $string . ' ' . $childrenCategory->getName() . '</option>';
                $this->getSubcategories($childrenCategory);
            }
        }
    }

    /**
     * @return array
     */
    public function getAutoresponders()
    {
        $params = ['query' => ['triggerType' => 'onday', 'status' => 'active']];
        $result = $this->getClient()->getAutoresponders($params);
        $autoresponders = [];

        if (!empty($result)) {
            foreach ($result as $autoresponder) {
                if (isset($autoresponder->triggerSettings->selectedCampaigns[0])) {
                    $autoresponders[$autoresponder->triggerSettings->selectedCampaigns[0]][$autoresponder->triggerSettings->dayOfCycle] = [
                        'name' => $autoresponder->name,
                        'subject' => $autoresponder->subject,
                        'dayOfCycle' => $autoresponder->triggerSettings->dayOfCycle
                    ];
                }
            }
        }

        return $autoresponders;
    }

    /**
     * @param $action
     *
     * @return string
     */
    public function getAction($action)
    {
        switch ($action) {
            case 'copy':
                return 'copied';
                break;

            case 'move':
                return 'moved';
                break;
        }
    }

    /**
     * @return array
     */
    public function getAutorespondersForFrontend()
    {
        $autoresponders = $this->getAutoresponders();

        if (empty($autoresponders)) {
            return [];
        }

        $result = [];

        foreach ($autoresponders as $id => $elements) {
            $array = [];
            foreach ($elements as $element) {
                $array[] = $element;
            }

            $result[$id] = $array;
        }
        return $result;
    }
}
