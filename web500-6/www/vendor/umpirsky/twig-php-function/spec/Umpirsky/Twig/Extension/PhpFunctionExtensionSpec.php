<?php

namespace spec\Umpirsky\Twig\Extension;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhpFunctionExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Umpirsky\Twig\Extension\PhpFunctionExtension');
    }

    function it_is_a_Twig_extension()
    {
        $this->shouldHaveType('Twig_Extension');
    }
}
