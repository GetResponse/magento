<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RuleFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollectionFactory;
use Magento\Framework\Data\Tree\Node;
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

    /** @var Getresponse */
    private $getresponseBlock;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Getresponse $getresponseBlock
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Getresponse $getresponseBlock
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->grRepository = $repositoryFactory->createRepository();
        $this->getresponseBlock = $getresponseBlock;
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

    /**
     * @param int $ruleId
     * @return Rule
     */
    public function getCurrentRule($ruleId = 0)
    {
        if (0 === $ruleId) {
            $ruleId = $this->_request->getParam('id');
        }

        return RuleFactory::createFromDbArray((array)$this->repository->getRuleById($ruleId));
    }

    /**
     * @return RulesCollection
     */
    public function getRulesCollection()
    {
        return RulesCollectionFactory::createFromRepository($this->repository->getRules());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCategoryName($id)
    {
        return $this->repository->getCategoryName($id);
    }

    /**
     * @return array
     */
    public function getAutoresponders()
    {
        return $this->getresponseBlock->getAutoresponders();
    }

    /**
     * @return array
     */
    public function getAutorespondersForFrontend()
    {
        return $this->getresponseBlock->getAutorespondersForFrontend();
    }

    /**
     * @param Node $node
     * @param $selectedCategory
     * @return string
     */
    public function getSubcategories(Node $node, $selectedCategory)
    {
        $result = '';

        if ($node->hasChildren()) {
            $childrenCategories = $node->getChildren();
            foreach ($childrenCategories as $childrenCategory) {
                $string = '';
                for ($i = $childrenCategory->getLevel(); $i > 2; $i--) {
                    $string .= '-';
                }

                $selected = $selectedCategory == $childrenCategory->getEntityId() ? 'selected="selected"' : '';

                $result .= '<option ' . $selected . ' value="' . $childrenCategory->getEntityId() . '"> ' .
                    $string . ' ' . $childrenCategory->getName() . '</option>';

                $result .= $this->getSubcategories($childrenCategory, $selectedCategory);
            }
        }

        return $result;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function getAction($action)
    {
        switch ($action) {
            case 'copy':
                return 'copied';

            case 'move':
                return 'moved';

            default:
                return '';
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function getCampaign($id)
    {
        return $this->grRepository->getCampaign($id);
    }
}
