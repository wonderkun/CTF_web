<?php

namespace Aptoma\Twig\Extension;

use Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine;

/**
 * @author Gunnar Liun <gunnar@aptoma.com>
 */
class MarkdownExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getParseMarkdownTests
     */
    public function testParseMarkdown($template, $expected, $context = array())
    {
        $this->assertEquals($expected, $this->getTemplate($template)->render($context));
    }

    public function getParseMarkdownTests()
    {
        return array(
            array('{{ "# Main Title"|markdown }}', '<h1>Main Title</h1>' . PHP_EOL),
            array('{{ content|markdown }}', '<h1>Main Title</h1>' . PHP_EOL, array('content' => '# Main Title'))
        );
    }

    protected function getEngine()
    {
        return new MichelfMarkdownEngine();
    }

    protected function getTemplate($template)
    {
        $loader = new \Twig_Loader_Array(array('index' => $template));
        $twig = new \Twig_Environment($loader, array('debug' => true, 'cache' => false));
        $twig->addExtension(new MarkdownExtension($this->getEngine()));

        return $twig->loadTemplate('index');
    }
}
