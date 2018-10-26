<?php

class PhpFunctionExtensionTest extends PHPUnit_Framework_TestCase
{
    private $twig;

    public function setUp()
    {
        $loader = new Twig_Loader_Array(array(
            'md5'   => '{{ md5("umpirsky") }} is md5 of umpirsky.',
            'floor' => '{{ floor(7.7) }} is floor of 7.7.',
            'ceil'  => '{{ ceil(6.7) }} is ceil of 6.7.',
        ));

        $this->twig = new Twig_Environment($loader);
        $this->twig->addExtension(new Umpirsky\Twig\Extension\PhpFunctionExtension());
    }

    /**
     * @dataProvider renderProvider
     */
    public function testRenderedOutput($key, $expected)
    {
        $this->assertEquals(
            $this->twig->render($key),
            $expected
        );
    }

    public function renderProvider()
    {
        return array(
            array('md5', 'f0d0a45e43690965bd6689a2ae3c8943 is md5 of umpirsky.'),
            array('floor', '7 is floor of 7.7.'),
            array('ceil', '7 is ceil of 6.7.'),
        );
    }
}
