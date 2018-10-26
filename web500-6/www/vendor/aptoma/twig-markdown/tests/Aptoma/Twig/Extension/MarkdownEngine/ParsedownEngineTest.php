<?php

namespace Aptoma\Twig\Extension\MarkdownEngine;

use Aptoma\Twig\Extension\MarkdownExtensionTest;

// Require parent class if not autoloaded
if (!class_exists('\Aptoma\Twig\Extension\MarkdownExtensionTest')) {
    require_once(__DIR__ . '/../MarkdownExtensionTest.php');
}

/**
 * Class ParsedownEngineTest
 *
 * @author Sébastien Lourseau <https://github.com/SebLours>
 */
class ParsedownEngineTest extends MarkdownExtensionTest
{
    public function getParseMarkdownTests()
    {
        return array(
            array('{{ "# Main Title"|markdown }}', '<h1>Main Title</h1>'),
            array('{{ content|markdown }}', '<h1>Main Title</h1>', array('content' => '# Main Title')),
            array('{% markdown %}{{ content }}{% endmarkdown %}', '<h1>Main Title</h1>', array('content' => '# Main Title'))
        );
    }

    protected function getEngine()
    {
        return new ParsedownEngine();
    }
}
