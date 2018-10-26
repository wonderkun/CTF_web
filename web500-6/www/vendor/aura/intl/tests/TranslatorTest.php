<?php
namespace Aura\Intl;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $translator;

    protected $factory;

    protected $package;

    protected $locale = 'en_US';

    protected $messages = [
        'TEXT_FOO' => 'Foo text',
        'TEXT_BAR' => 'Bar text',
    ];

    protected $formatter;

    protected function newTranslator(TranslatorInterface $fallback = null)
    {
        return $this->factory->newInstance(
            'en_US',
            $this->package,
            $this->formatter,
            $fallback
        );
    }

    protected function setUp()
    {
        $this->factory = new TranslatorFactory;
        $this->formatter = new MockFormatter;
        $this->package = new Package;
        $this->package->setMessages($this->messages);
        $this->translator = $this->newTranslator();
    }

    public function testTranslate()
    {
        // key exists
        $expect = 'Foo text';
        $actual = $this->translator->translate('TEXT_FOO');
        $this->assertSame($expect, $actual);

        // key exists, with tokens passed
        $expect = 'Foo text';
        $actual = $this->translator->translate('TEXT_FOO', ['foo' => 'bar']);
        $this->assertSame($expect, $actual);

        // key does not exist
        $expect = 'TEXT_NONE';
        $actual = $this->translator->translate('TEXT_NONE');
        $this->assertSame($expect, $actual);
    }

    public function testTranslate_fallback()
    {
        $package = new Package;
        $package->setMessages([
            'TEXT_NONE' => 'Fallback text',
        ]);
        // create fallback translator
        $fallback = new Translator(
            'en_US',
            $package,
            $this->formatter
        );

        // create primary translator with fallback
        $translator = $this->newTranslator($fallback);

        // key does not exist in primary, but exists in fallback
        $expect = 'Fallback text';
        $actual = $translator->translate('TEXT_NONE');
        $this->assertSame($expect, $actual);
    }

    public function testTranslateMissingKey()
    {
        // using getMockBuilder so we can use with phpunit 4.8 or 5+ without warnings
        $formatter = $this->getMockBuilder(get_class($this->formatter))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
        // create fallback translator
        $translator = new Translator('en_US', new Package, $formatter);

        $formatter->expects($this->once())
            ->method('format')
            ->with('en_US', 'TEXT', ['var' => 'SOME'])
            ->will($this->returnValue('FORMATTED'));

        // key does not exist, with tokens passed
        $expect = 'FORMATTED';
        $actual = $translator->translate('TEXT', ['var' => 'SOME']);
        $this->assertEquals($expect, $actual);
    }
}
