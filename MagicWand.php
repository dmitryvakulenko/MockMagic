<?php
/**
 * Created by IntelliJ IDEA.
 * User: n0dwis
 * Date: 8/2/13
 * Time: 8:36 AM
 * To change this template use File | Settings | File Templates.
 */

namespace MockLibrary;


class MagicWand {

    /**
     * @var MagicWand
     */
    private static $_instance;

    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * @param $name
     * @param $arguments
     * @return CallInfo
     */
    public function __call($name, $arguments) {
        $info = new CallInfo();
        $info->methodName = $name;
        $info->arguments = $arguments;

        return $info;
    }
}