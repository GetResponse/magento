<?php

namespace GetResponse\GetResponseIntegration\Test;

use PHPUnit_Framework_TestCase;

class BaseTestCase extends PHPUnit_Framework_TestCase
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