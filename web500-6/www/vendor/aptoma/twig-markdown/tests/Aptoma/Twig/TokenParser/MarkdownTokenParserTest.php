<?php

namespace Aptoma\Twig\TokenParser;

use Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine;
use Aptoma\Twig\Node\MarkdownNode;

/**
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class MarkdownTokenParserTest extends \Twig_Test_NodeTestCase
{
    public function testConstructor()
    {
        $body = new \Twig_Node(array(new \Twig_Node_Text("#Title\n\nparagraph\n", 1)));
        $node = new MarkdownNode($body, 1);

        $this->assertEquals($body, $node->getNode('body'));
    }

    /**
     * Test that the generated code actually do what we expect
     *
     * The contents of this test is the same that we write in the compile method.
     * This requires manual synchronization, which we should probably not rely on.
     */
    public function testMarkdownPrepareBehavior()
    {
        $body = "    #Title\n\n    paragraph\n\n        code";
        $bodyPrepared = "#Title\n\nparagraph\n\n    code";

        ob_start();
        echo $body;
        $content = ob_get_clean();
        preg_match("/^\s*/", $content, $matches);
        $lines = explode("\n", $content);
        $content = preg_replace('/^' . $matches[0]. '/', "", $lines);
        $content = join("\n", $content);

        // Assert prepared content looks right
        $this->assertEquals($bodyPrepared, $content);

        // Assert Markdown output
        $expectedOutput = "<h1>Title</h1>\n\n<p>paragraph</p>\n\n<pre><code>code\n</code></pre>\n";
        $this->assertEquals($expectedOutput, $this->getEngine()->transform($content));
    }

    /**
     * Test that the generated code looks as expected
     *
     * @dataProvider getTests
     */
    public function testCompile($node, $source, $environment = null)
    {
        parent::testCompile($node, $source, $environment);
    }

    protected function getEngine()
    {
        return new MichelfMarkdownEngine();
    }

    public function getTests()
    {
        $tests = array();

        $body = new \Twig_Node(array(new \Twig_Node_Text("#Title\n\nparagraph\n", 1)));
        $node = new MarkdownNode($body, 1);

        $tests['simple text'] = array($node, <<<EOF
// line 1
ob_start();
echo "#Title

paragraph
";
\$content = ob_get_clean();
preg_match("/^\s*/", \$content, \$matches);
\$lines = explode("\\n", \$content);
\$content = preg_replace('/^' . \$matches[0]. '/', "", \$lines);
\$content = join("\\n", \$content);
echo \$this->env->getTokenParsers()->getTokenParser('markdown')->getEngine()->transform(\$content);
EOF
            );

        $body = new \Twig_Node(array(new \Twig_Node_Text("    #Title\n\n    paragraph\n\n        code\n", 1)));
        $node = new MarkdownNode($body, 1);

        $tests['text with leading indent'] = array($node, <<<EOF
// line 1
ob_start();
echo "    #Title

    paragraph

        code
";
\$content = ob_get_clean();
preg_match("/^\s*/", \$content, \$matches);
\$lines = explode("\\n", \$content);
\$content = preg_replace('/^' . \$matches[0]. '/', "", \$lines);
\$content = join("\\n", \$content);
echo \$this->env->getTokenParsers()->getTokenParser('markdown')->getEngine()->transform(\$content);
EOF
        );

        return $tests;
    }
}
