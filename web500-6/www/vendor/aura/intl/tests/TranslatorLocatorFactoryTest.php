<?php
namespace Aura\Intl;

class TranslatorLocatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function test__newInstance()
    {
        $factory = new TranslatorLocatorFactory();
        $this->assertInstanceOf('Aura\Intl\TranslatorLocator', $factory->newInstance());
    }
}
