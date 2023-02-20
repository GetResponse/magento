<?php declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected function getMockWithoutConstructing(
        string $name,
        array $existedMethodsToMock = [],
        array $newMethodsToMock = []
    ): MockObject {
        $mock = $this->getMockBuilder($name)
            ->disableOriginalConstructor();

        if (0 < count($existedMethodsToMock)) {
            $mock->onlyMethods($existedMethodsToMock);
        }

        if (0 < count($newMethodsToMock)) {
            $mock->addMethods($newMethodsToMock);
        }

        return $mock->getMock();
    }
}
