<?php
namespace MockMagic;

use InvalidArgumentException;
use PHPUnit_Framework_Constraint as Constraint;
use PHPUnit_Framework_MockObject_Matcher_Invocation as Invokation;
use PHPUnit_Framework_MockObject_Stub as Stub;

class MockBuilder {

    /**
     * @var \PHPUnit_Framework_TestCase
     */
    private $_test;

    private $_class;

    private $_methods = array();

    function __construct(\PHPUnit_Framework_TestCase $test, $class) {
        $this->_test = $test;
        $this->_class = $class;
    }


    /**
     * @param null $info этот аргумент nullable просто чтобы не подчеркивала idea (не всегда помогает :)
     * @param null $result
     * @param null $matcher
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function on($info = null, $result = null, $matcher = null) {
        if (!($info instanceof CallInfo)) {
            throw new InvalidArgumentException("First argument of method 'on' should be a CallInfo type. " . get_class($info) . " instead.");
        }
        $name = $info->methodName;
        $this->_methods[$name] = array();

        if (isset($result)) {
            $this->_methods[$name]['returns'] = $this->_ensureReturnIsStub($result);
        }
        $this->_methods[$name]['matcher'] = $this->_ensureMatcherIsInvokeCounter($matcher);
        $this->_methods[$name]['arguments'] = $this->_ensureArgumentsIsConstraint($info->arguments);

        return $this;
    }

    public function createPartialMock() {
        $mockedMethods = array_keys($this->_methods);
        $mock = $this->_createMock($mockedMethods);
        foreach ($mockedMethods as $methodName) {
            $this->_setUpMethodMock($mock, $methodName);
        }
        return $mock;
    }

    public function createFullMock() {
        $mockedMethods = $this->_getAllClassMethods();
        $mock = $this->_createMock($mockedMethods);
        foreach ($mockedMethods as $methodName) {
            if (isset($this->_methods[$methodName])) {
                $this->_setUpMethodMock($mock, $methodName);
            } else {
                $mock->expects($this->_test->never())
                    ->method($methodName);
            }
        }
        return $mock;
    }

    private function _createMock($mockedMethods) {
        $mock = $this->_test->getMockBuilder($this->_class)
            ->disableOriginalConstructor()
            ->setMethods($mockedMethods)
            ->getMock();

        return $mock;
    }

    private function _getAllClassMethods() {
        $res = array();
        $ref = new \ReflectionClass($this->_class);
        $allMethods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($allMethods as $method) {
            $methodName = $method->getName();
            if (substr_compare($methodName, '__', 0, 2) == 0) {
                continue;
            }
            $res[] = $methodName;
        }

        return $res;
    }

    private function _setUpMethodMock($mock, $methodName) {
        $methodData = $this->_methods[$methodName];
        $invMocker = $mock->expects($methodData['matcher'])
            ->method($methodName);

        if (isset($methodData['returns'])) {
            $invMocker->will($methodData['returns']);
        }

        if (!empty($methodData['arguments'])) {
            call_user_func_array(array($invMocker, 'with'), $methodData['arguments']);
        }
    }

    private function _ensureArgumentsIsConstraint($arguments) {
        $res = array();
        foreach ($arguments as $arg) {
            // PHPUnit makes it themself but I feel myself tranquilitied
            if ($arg instanceof Constraint) {
                $res[] = $arg;
            } else {
                $res[] = $this->_test->equalTo($arg);
            }
        }

        return $res;
    }


    private function _ensureReturnIsStub($value) {
        if ($value instanceof Stub) {
            return $value;
        } else {
            return $this->_test->returnValue($value);
        }
    }

    private function _ensureMatcherIsInvokeCounter($matcher = null) {
       if ($matcher instanceof Invokation) {
            return $matcher;
        } elseif (is_null($matcher)) {
            return $this->_test->atLeastOnce();
        } else {
           throw new InvalidArgumentException('Matcher should be instance of PHPUnit_Framework_MockObject_Matcher_InvokedRecorder or null.');
       }
    }
}