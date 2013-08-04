<?php
namespace MockMagic;


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