<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Account as GrAccount;
use GetResponse\GetResponseIntegration\Domain\GetResponse\AccountFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsCollectionFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\RegistrationSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;

/**
 * Class Getresponse
 * @package GetResponse\GetResponseIntegration\Block
 */
class Getresponse
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @return array
     */
    public function getAutoresponders()
    {
        try {
            $grRepository = $this->repositoryFactory->createRepository();
            $params = ['query' => ['triggerType' => 'onday', 'status' => 'active']];
            $result = $grRepository->getAutoresponders($params);
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
        } catch (RepositoryException $e) {
            return [];
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

    /**
     * @return RegistrationSettings
     */
    public function getRegistrationSettings()
    {
        return RegistrationSettingsFactory::createFromArray(
            $this->repository->getRegistrationSettings()
        );
    }

    /**
     * @return CustomFieldsCollection
     */
    public function getCustoms()
    {
        return CustomFieldsCollectionFactory::createFromRepository($this->repository->getCustoms());
    }

    /**
     * @return GrAccount
     */
    public function getAccountInfo()
    {
        return AccountFactory::createFromArray($this->repository->getAccountInfo());
    }

}
