<?php
namespace Beakman;

class FunctionNames
{
    public function _forbidden()
    {
        echo "I emit an error";
    }

    private function notDiscouraged()
    {
        echo "I don't emit a warning anymore";
    }

    public function setVariables()
    {
        $this->passingPublic = 'changed';
        $this->underscored = 'has value now';
        $this->doubleUnderscore = 'not recommended';
    }

    public static function setStatics()
    {
        self::$publicStatic = true;
        self::$protectedStatic = true;
        self::$privateStatic = true;
    }

    protected function _someFunc()
    {
        // code here
    }

    protected function noUnderscorePrefix()
    {
        // code here
        $closure = function () {
            // code here
        };
    }

    public function __call($name, $arguments)
    {
    }
    public function __construct()
    {
    }
    public function __clone()
    {
    }
    public function __debugInfo()
    {
    }
    public function __destruct()
    {
    }
    public function __get($name)
    {
    }
    public function __invoke()
    {
    }
    public function __isset($name)
    {
    }
    public function __set($name, $value)
    {
    }
    public function __sleep()
    {
    }
    public function __toString()
    {
        return '';
    }
    public function __unset($name)
    {
    }
    public function __wakeup()
    {
    }
}

interface FunctionNamesInterface
{
    public function _forbidden();

    public function setVariables();

    public static function setStatics();
}

trait FunctionNamesTrait
{
    public function _forbidden()
    {
        echo "I emit an error";
    }

    private function notDiscouraged()
    {
        echo "I don't emit a warning anymore";
    }

    public function setVariables()
    {
        $this->passingPublic = 'changed';
        $this->underscored = 'has value now';
        $this->doubleUnderscore = 'not recommended';
    }

    public static function setStatics()
    {
        self::$publicStatic = true;
        self::$protectedStatic = true;
        self::$privateStatic = true;
    }

    protected function _someFunc()
    {
        // code here
    }

    protected function noUnderscorePrefix()
    {
        // code here
    }

    function _noScopeSpecified()
    {
        echo 'handled by an other Sniff';
    }

    public function __call($name, $arguments)
    {
    }
    public function __construct()
    {
    }
    public function __clone()
    {
    }
    public function __debugInfo()
    {
    }
    public function __destruct()
    {
    }
    public function __get($name)
    {
    }
    public function __invoke()
    {
    }
    public function __isset($name)
    {
    }
    public function __set($name, $value)
    {
    }
    public function __sleep()
    {
    }
    public function __toString()
    {
        return '';
    }
    public function __unset($name)
    {
    }
    public function __wakeup()
    {
    }
}

public function __passingOutside() {}
