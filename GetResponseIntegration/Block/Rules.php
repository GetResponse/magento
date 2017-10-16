<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class Rules
 * @package GetResponse\GetResponseIntegration\Block
 */
class Rules extends GetResponse
{
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

    public function getAutomationByParam($param)
    {
        return $this->repository->getAutomationByParam(
            $this->getRequest()->getParam($param), $param
        );
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
