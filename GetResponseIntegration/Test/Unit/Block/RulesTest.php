<?php
namespace GetResponse\GetResponseIntegration\Test\Unit\Block;

use GetResponse\GetResponseIntegration\Block\Getresponse;
use GetResponse\GetResponseIntegration\Block\Rules as RulesBlock;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Rule;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RulesCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit_Framework_MockObject_MockObject;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;


/**
 * Class RulesTest
 * @package GetResponse\GetResponseIntegration\Test\Unit\Block
 */
class RulesTest extends TestCase
{
    /** @var Context|PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var Repository|PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    /** @var RepositoryFactory|PHPUnit_Framework_MockObject_MockObject */
    private $repositoryFactory;

    /** @var RulesBlock */
    private $ruleBlock;

    /** @var GrRepository|PHPUnit_Framework_MockObject_MockObject */
    private $grRepository;

    /** @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    public function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->repository = $this->createMock(Repository::class);
        $this->repositoryFactory = $this->createMock(RepositoryFactory::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->grRepository = $this->createMock(GrRepository::class);
        $this->repositoryFactory->method('createRepository')->willReturn($this->grRepository);

        $getresponseBlock = new Getresponse($this->repository, $this->repositoryFactory);
        $this->ruleBlock = new RulesBlock($this->context, $this->objectManager, $this->repository, $this->repositoryFactory, $getresponseBlock);
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
            [[], new Rule(0, 0, '', '', 0)],
            [
                [
                    'id' => 3,
                    'category' => 4,
                    'action' => 'action',
                    'campaign' => 'x4g',
                    'cycle_day' => 8
                ], new Rule(3, 4, 'action', 'x4g', 8)
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

        $collection = new RulesCollection();
        $collection->add(new Rule(4, 3, 'simple-action', 'x4g', 8));

        return [
            [[], new RulesCollection()],
            [[$rule], $collection]
        ];
    }

    /**
     * @test
     */
    public function shouldReturnAutoresponders()
    {
        $campaignId = 'x3v';
        $name = 'testName';
        $subject = 'testSubject';
        $dayOfCycle = 5;

        $triggerSettings = new \stdClass();
        $triggerSettings->selectedCampaigns = [$campaignId];
        $triggerSettings->dayOfCycle = $dayOfCycle;

        $rawAutoresponder = new \stdClass();
        $rawAutoresponder->triggerSettings = $triggerSettings;
        $rawAutoresponder->name = $name;
        $rawAutoresponder->subject = $subject;

        $rawAutoresponders = [
            $rawAutoresponder
        ];
        $this->grRepository->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoresponders);

        $autoresponders = $this->ruleBlock->getAutoresponders();
        self::assertTrue(is_array($autoresponders));

        if (count($autoresponders) > 0) {
            self::assertEquals($name, $autoresponders[$campaignId][$dayOfCycle]['name']);
            self::assertEquals($subject, $autoresponders[$campaignId][$dayOfCycle]['subject']);
            self::assertEquals($dayOfCycle, $autoresponders[$campaignId][$dayOfCycle]['dayOfCycle']);
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

        $triggerSettings = new \stdClass();
        $triggerSettings->selectedCampaigns = [$campaignId];
        $triggerSettings->dayOfCycle = $dayOfCycle;

        $rawAutoresponder = new \stdClass();
        $rawAutoresponder->triggerSettings = $triggerSettings;
        $rawAutoresponder->name = $name;
        $rawAutoresponder->subject = $subject;

        $rawAutoresponders = [
            $rawAutoresponder
        ];
        $this->grRepository->expects($this->once())->method('getAutoresponders')->willReturn($rawAutoresponders);

        $autoresponders = $this->ruleBlock->getAutorespondersForFrontend();

        self::assertTrue(is_array($autoresponders));

        if (count($autoresponders) > 0) {
            self::assertEquals($name, $autoresponders[$campaignId][0]['name']);
            self::assertEquals($subject, $autoresponders[$campaignId][0]['subject']);
            self::assertEquals($dayOfCycle, $autoresponders[$campaignId][0]['dayOfCycle']);
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
