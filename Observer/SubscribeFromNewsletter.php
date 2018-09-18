<?php
namespace GetResponse\GetResponseIntegration\Observer;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\ApiHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubscribeFromNewsletter
 * @package GetResponse\GetResponseIntegration\Observer
 */
class SubscribeFromNewsletter implements ObserverInterface
{
    /** @var ObjectManagerInterface */
    protected $_objectManager;

    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var Session */
    private $session;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     * @param Session $session
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Repository $repository,
        RepositoryFactory $repositoryFactory,
        Session $session
    ) {
        $this->_objectManager = $objectManager;
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
        $this->session = $session;
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

            $apiHelper = new ApiHelper($grRepository);

            $subscriber = $observer->getEvent()->getSubscriber();
            $email = $subscriber->getEmail();

            if (empty($email)) {
                return $this;
            }

            $params = [];
            $userCustoms = [];
            $userCustoms['origin'] = 'magento2';
            $params['campaign'] = ['campaignId' => $newsletterSettings->getCampaignId()];
            $params['email'] = $email;

            if (!empty($newsletterSettings->getCycleDay())) {
                $params['dayOfCycle'] = (int) $newsletterSettings->getCycleDay();
            }

            $contact = $grRepository->getContactByEmail($email, $newsletterSettings->getCampaignId());

            if (!isset($contact['contactId'])) {
                $params['customFieldValues'] = $apiHelper->setCustoms($userCustoms);
                $grRepository->addContact($params);
            }
            return $this;
        } catch (RepositoryException $e) {
            return $this;
        }
    }
}
