<?php

namespace GetResponse\GetResponseIntegration\Test;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @param string $name
     * @param string[] $methodsToOverride
     * @return \PHPUnit_Framework_MockObject_MockObject|object
     */
    protected function getMockWithoutConstructing($name, array $methodsToOverride = [])
    {
        return $this->getMockBuilder($name)
            ->setMethods($methodsToOverride)
            ->disableOriginalConstructor()
            ->getMock();
    }
}