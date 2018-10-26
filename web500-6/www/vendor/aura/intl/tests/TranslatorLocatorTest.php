<?php
namespace Aura\Intl;

class TranslatorLocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected $translators;

    protected $packages;

    protected $formatters;

    protected function setUp()
    {
        $registry['Vendor.Package']['en_US'] = function () {
            return new \Aura\Intl\Package(
                'mock',
                null,
                [
                    'ERR_NO_SUCH_OPTION' => "The option {option} is not recognized.",
                ]
            );
        };

        $registry['Vendor.Package']['pt_BR'] = function () {
            return new \Aura\Intl\Package(
                'mock',
                null,
                [
                    'ERR_NO_SUCH_OPTION' => "O {option} opção não é reconhecido.",
                ]
            );
        };

        $this->packages = new PackageLocator($registry);

        $this->formatters = new FormatterLocator([
            'mock' => function () {
                return new MockFormatter;
            },
        ]);

        $this->factory = new TranslatorFactory;

        $this->translators = new TranslatorLocator(
            $this->packages,
            $this->formatters,
            $this->factory,
            'en_US'
        );
    }

    public function testSetAndGetLocale()
    {
        $expect = 'pt_BR';
        $this->translators->setLocale($expect);
        $actual = $this->translators->getLocale();
        $this->assertSame($expect, $actual);
    }

    public function testGetFactory()
    {
        $actual = $this->translators->getFactory();
        $this->assertSame($this->factory, $actual);
    }

    public function testGet()
    {
        $actual = $this->translators->get('Vendor.Package');
        $this->assertInstanceOf('Aura\Intl\Translator', $actual);
    }

    public function testGetPackages()
    {
        $actual = $this->translators->getPackages();
        $this->assertSame($this->packages, $actual);
    }

    public function testGetFormatterLocator()
    {
        $actual = $this->translators->getFormatters();
        $this->assertSame($this->formatters, $actual);
    }

    public function testIssue9()
    {
        $this->packages->set('Vendor.Package', 'en_UK', function () {
            $package = new Package('mock');
            $package->setMessages([
                'FOO' => 'The text for "foo."',
                'BAR' => 'The text for "bar."',
            ]);
            return $package;
        });

        $translator = $this->translators->get('Vendor.Package', 'en_UK');
        $expect = 'The text for "foo."';
        $this->assertSame($translator->translate('FOO'), $expect);
    }

    public function testIssue9Failure()
    {
        $package = new Package;
        $package->setMessages([
            'FOO' => 'The text for "foo."',
            'BAR' => 'The text for "bar."',
        ]);
        // $this->packages->set('Vendor.Package', 'en_UK', $package);
        // $this->setExpectedException('Exception');
        // $translator = $this->translators->get('Vendor.Package', 'en_UK');
    }
}
