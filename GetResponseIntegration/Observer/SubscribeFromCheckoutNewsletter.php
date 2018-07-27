<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class SubscribeFromCheckoutNewsletter
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromCheckoutNewsletter implements ObserverInterface
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
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $newsletterSettings = NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings()
        );

        if (!$newsletterSettings->isEnabled()) {
            return $this;
        }

        try {
            $grRepository = $this->repositoryFactory->createRepository();
        } catch (RepositoryException $e) {
            return $this;
        }

        $apiHelper = new ApiHelper($grRepository);

        $subscriber = $observer->getEvent()->getSubscriber();
        $email = $subscriber->getEmail();

        if (empty($email)) {
            return $this;
        }

        $params = [];
        $user_customs = [];
        $user_customs['origin'] = 'magento2';
        $params['campaign'] = ['campaignId' => $newsletterSettings->getCampaignId()];
        $params['email'] = $email;

        if (!empty($newsletterSettings->getCycleDay())) {
            $params['dayOfCycle'] = (int)$newsletterSettings->getCycleDay();
        }

        $contact = $grRepository->getContactByEmail($email, $newsletterSettings->getCampaignId());

        if (empty($contact)) {
            $params['customFieldValues'] = $apiHelper->setCustoms($user_customs);
            $grRepository->addContact($params);
        }
        return $this;
    }
}
