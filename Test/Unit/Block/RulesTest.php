<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Block\Rules as RulesBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;
use GrShareCode\GetresponseApiClient;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;


/**
 * Class RulesTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class RulesTest extends BaseTestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var RulesBlock */
    private $ruleBlock;

    /** @var GetresponseApiClient|PHPUnit_Framework_MockObject_MockObject */
    private $grRepository;

    /** @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    public function setUp()
    {
        $this->context = $this->getMockWithoutConstructing(Context::class);
        $this->repository = $this->getMockWithoutConstructing(Repository::class);
        $this->repositoryFactory = $this->getMockWithoutConstructing(RepositoryFactory::class);
        $this->objectManager = $this->getMockWithoutConstructing(ObjectManagerInterface::class);
        $this->grRepository = $this->getMockWithoutConstructing(GetresponseApiClient::class);
        $this->repositoryFactory->method('createGetResponseApiClient')->willReturn($this->grRepository);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->ruleBlock = new RulesBlock($this->context,$this->repository, $this->repositoryFactory, $getresponseBlock);
    }

    /**
     * @test
     * @param array $rawRule
     * @param Rule $expectedRule
     *
     * @dataProvider shouldReturnCurrentRuleProvider
     */
    public function shouldReturnCurrentRule(array $rawRule, Rule $expectedRule)
    {
        $this->repository->expects($this->once())->method('getRuleById')->willReturn($rawRule);
        $rule = $this->ruleBlock->getCurrentRule(1);

        self::assertEquals($expectedRule, $rule);
    }

    /**
     * @return array
     */
    public function shouldReturnCurrentRuleProvider()
    {
        return [
            [[], new Rule(0, 0, '', '', 0, '')],
            [
                [
                    'id' => 3,
                    'category' => 4,
                    'action' => 'action',
                    'campaign' => 'x4g',
                    'cycle_day' => 8,
                    'autoresponderId' => 'XyD'
                ], new Rule(3, 4, 'action', 'x4g', 8, 'XyD')
            ]
        ];
    }

    /**
     * @test
     * @param array $rawRules
     * @param RulesCollection $expectedRulesCollection
     *
     * @dataProvider shouldReturnRulesCollectionProvider
     */
    public function shouldReturnRulesCollection(array $rawRules, RulesCollection $expectedRulesCollection)
    {
        $this->repository->expects($this->once())->method('getRules')->willReturn($rawRules);
        $rulesCollection = $this->ruleBlock->getRulesCollection();

        self::assertEquals($expectedRulesCollection, $rulesCollection);
    }

    /**
     * @return array
     */
    public function shouldReturnRulesCollectionProvider()
    {
        $rule = new \stdClass();
        $rule->id = 4;
        $rule->category = 3;
        $rule->action = 'simple-action';
        $rule->campaign = 'x4g';
        $rule->cycle_day = 8;
        $rule->autoresponderId = 'u7A';

        $collection = new RulesCollection();
        $collection->add(new Rule(4, 3, 'simple-action', 'x4g', 8,  'u7A'));

        return [
            [[], new RulesCollection()],
            [[$rule], $collection]
        ];
    }

    /**
     * @test
     */
    public function shouldReturnAutoResponders()
    {
        $campaignId = 'x3v';
        $name = 'testName';
        $subject = 'testSubject';
        $dayOfCycle = 5;
        $responderId = 'q31';

        $triggerSettings = [
            'selectedCampaigns' => [$campaignId],
            'dayOfCycle' => $dayOfCycle
        ];

        $rawAutoResponder = [
            'autoresponderId' => $responderId,
            'campaignId' => $campaignId,
            'status' => 'enabled',
            'triggerSettings' => $triggerSettings,
            'name' => $name,
            'subject' => $subject
        ];

        $rawAutoResponders = [
            $responderId => $rawAutoResponder
        ];

        $this->grRepository->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoResponders);

        $autoResponders = $this->ruleBlock->getAutoResponders();
        self::assertTrue(is_array($autoResponders));

        if (count($autoResponders) > 0) {
            self::assertEquals($name, $autoResponders[$campaignId][$responderId]['name']);
            self::assertEquals($subject, $autoResponders[$campaignId][$responderId]['subject']);
            self::assertEquals($dayOfCycle, $autoResponders[$campaignId][$responderId]['dayOfCycle']);
        }
    }

    /**
     * @test
     */
    public function shouldReturnAutorespondersForFrontend()
    {
        $campaignId = 'x3v';
        $name = 'testName';
        $subject = 'testSubject';
        $dayOfCycle = 5;
        $responderId = 'q31';

        $triggerSettings = [
            'selectedCampaigns' => [$campaignId],
            'dayOfCycle' => $dayOfCycle
        ];

        $rawAutoResponder = [
            'autoresponderId' => $responderId,
            'campaignId' => $campaignId,
            'status' => 'enabled',
            'triggerSettings' => $triggerSettings,
            'name' => $name,
            'subject' => $subject
        ];

        $rawAutoResponders = [
            $responderId => $rawAutoResponder
        ];

        $this->grRepository->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoResponders);

        $autoResponders = $this->ruleBlock->getAutoRespondersForFrontend();

        self::assertTrue(is_array($autoResponders));

        if (count($autoResponders) > 0) {
            self::assertEquals($name, $autoResponders[$campaignId][$responderId]['name']);
            self::assertEquals($subject, $autoResponders[$campaignId][$responderId]['subject']);
            self::assertEquals($dayOfCycle, $autoResponders[$campaignId][$responderId]['dayOfCycle']);
        }
    }

    /**
     * @test
     */
    public function shouldReturnValidAction()
    {
        self::assertEquals('copied', $this->ruleBlock->getAction('copy'));
        self::assertEquals('moved', $this->ruleBlock->getAction('move'));
    }
}
