<?php
namespace Aura\Intl;

class BasicFormatterTest extends \PHPUnit_Framework_TestCase
{
    protected function newFormatter()
    {
        return new BasicFormatter;
    }

    public function testFormat()
    {
        $formatter = $this->newFormatter();

        $locale = 'en_US';
        $expect = 'Hello world 88!';
        $tokens_values = ['foo' => 'world', 'bar' => '88', 'baz' => '!'];

        $string = 'Hello {foo} {bar}{baz}';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);

        $tokens_values = ['array' => ['foo', 'bar', 'baz']];
        $string = 'Array {array}';
        $expect = 'Array "foo", "bar", "baz"';
        $actual = $formatter->format($locale, $string, $tokens_values);
        $this->assertSame($expect, $actual);
    }
}
