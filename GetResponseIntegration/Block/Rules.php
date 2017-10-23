<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RuleFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\View\Element\Template;

/**
 * Class Rules
 * @package GetResponse\GetResponseIntegration\Block
 */
class Rules extends Template
{
    /** @var Repository */
    private $repository;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    )
    {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->buildRepository();
    }

    /**
     * @return mixed
     */
    public function getStoreCategories()
    {
        return $this->repository->getStoreCategories();
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->grRepository->getCampaigns(['sort' => ['name' => 'asc']]);
    }

    public function getRuleById()
    {
        $id = $this->_request->getParam('id');

        if (empty($id)) {
            return null;
        }

        return RuleFactory::buildFromRepository($this->repository->getRuleById($id));
    }

    /**
     * @return RulesCollection
     */
    public function getRulesCollection()
    {
        return RulesCollectionFactory::buildFromRepository($this->repository->getRules());
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
     * @return array
     */
    public function getAutoresponders()
    {
        $params = ['query' => ['triggerType' => 'onday', 'status' => 'active']];
        $result = $this->grRepository->getAutoresponders($params);
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

    /**
     * @param $category \Magento\Catalog\Helper\Category|Category
     * @param $selectedCategory
     */
    public function getSubcategories($category, $selectedCategory)
    {
        if ($category->hasChildren()) {
            $childrenCategories = $category->getChildren();
            foreach ($childrenCategories as $childrenCategory) {
                $string = '';
                for ($i = $childrenCategory->getLevel(); $i > 2; $i--) {
                    $string .= '-';
                }

                $selected = $selectedCategory == $childrenCategory->getEntityId() ? 'selected="selected"' : '';

                echo '<option '.$selected.' value="' . $childrenCategory->getEntityId() . '"> ' .
                    $string . ' ' . $childrenCategory->getName() . '</option>';
                $this->getSubcategories($childrenCategory, $selectedCategory);
            }
        }
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

        return '';
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCampaign($id)
    {
        return $this->grRepository->getCampaign($id);
    }
}
