<?php declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @param string[] $methodsToOverride
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $name
     * @psalm-return MockObject|RealInstanceType
     * @return MockObject
     */

    protected function getMockWithoutConstructing(string $name)
    {
        return $this->getMockBuilder($name)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
