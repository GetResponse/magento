<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseRepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromRegister
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromRegister implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    )
    {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $settings = $this->repository->getSettings();

        if ($settings['active_subscription'] != true) {
            return $this;
        }

        try {
            $grRepository = $this->repositoryFactory->buildRepository();
        } catch (GetResponseRepositoryException $e) {
            return $this;
        }

        $apiHelper = new ApiHelper($grRepository);

        $customer = $observer->getEvent()->getCustomer();

        $subscriber = $this->repository->loadSubscriberByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {

            $params = [];
            $params['campaign'] = ['campaignId' => $settings['campaign_id']];
            $params['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            $params['email'] = $customer->getEmail();

            if (isset($settings['cycle_day'])) {
                $params['dayOfCycle'] = (int)$settings['cycle_day'];
            }

            $params['customFieldValues'] = $apiHelper->setCustoms(array('origin' => 'magento2'));
            $grRepository->addContact($params);
        }

        return $this;
    }
}
