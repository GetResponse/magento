<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\ConnectionSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;


/**
 * Class Registration
 * @package GetResponse\GetResponseIntegration\Block
 */
class Registration extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var GrRepository */
    private $grRepository;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    )
    {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->grRepository = $repositoryFactory->buildRepository();
    }

    /**
     * @return mixed
     */
    public function getCampaigns()
    {
        return $this->grRepository->getCampaigns(['sort' => ['name' => 'asc']]);
    }

    /**
     * @return ConnectionSettings
     */
    public function getConnectionSettings()
    {
        return ConnectionSettingsFactory::buildFromRepository(
            $this->repository->getConnectionSettings()
        );
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

    public function getCustoms()
    {
        return CustomFieldsCollectionFactory::buildFromRepository($this->repository->getCustoms());
    }

    /**
     * @return RegistrationSettings
     */
    public function getRegistrationSettings()
    {
        return RegistrationSettingsFactory::createFromRepository(
            $this->repository->getRegistrationSettings()
        );
    }
}
