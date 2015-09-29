<?php

namespace MockMagic;


class CallInfo {

    /**
     * @var string
     */
    public $methodName;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
     */
    public $arguments = array();
}