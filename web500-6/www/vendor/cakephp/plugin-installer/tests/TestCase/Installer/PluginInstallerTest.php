<?php
namespace Cake\Test\TestCase\Composer\Installer;

use Cake\Test\Composer\Installer\PluginInstaller;
use Composer\Composer;
use Composer\Package\Package;
use Composer\Repository\RepositoryManager;
use PHPUnit\Framework\TestCase;

class PluginInstallerTest extends TestCase
{

    public $package;

    public $installer;

    /**
     * Directories used during tests
     *
     * @var string
     */
    protected $testDirs = [
        '',
        'vendor',
        'plugins',
        'plugins/Foo',
        'plugins/Fee',
        'plugins/Foe',
        'plugins/Fum',
        'app_plugins',
        'app_plugins/Bar',
    ];

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->package = new Package('CamelCased', '1.0', '1.0');
        $this->package->setType('cakephp-plugin');

        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'plugin-installer-test';

        foreach ($this->testDirs as $dir) {
            if (!is_dir($this->path . '/' . $dir)) {
                mkdir($this->path . '/' . $dir);
            }
        }

        $composer = new Composer();
        $config = $this->getMockBuilder('Composer\Config')->getMock();
        $config->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->path . '/vendor'));
        $composer->setConfig($config);

        $this->io = $this->getMockBuilder('Composer\IO\IOInterface')->getMock();
        $rm = new RepositoryManager(
            $this->io,
            $config
        );
        $composer->setRepositoryManager($rm);

        $this->installer = new PluginInstaller($this->io, $composer);
    }

    public function tearDown()
    {
        parent::tearDown();
        $dirs = array_reverse($this->testDirs);

        if (is_file($this->path . '/vendor/cakephp-plugins.php')) {
            unlink($this->path . '/vendor/cakephp-plugins.php');
        }

        foreach ($dirs as $dir) {
            if (is_dir($this->path . '/' . $dir)) {
                rmdir($this->path . '/' . $dir);
            }
        }
    }

    /**
     * Sanity test
     *
     * The test double should return a path to a test file, where
     * the containing folder
     *
     * @return void
     */
    public function testConfigFile()
    {
        $path = PluginInstaller::configFile("");
        $this->assertFileExists(dirname($path));
    }

    /**
     * Ensure that primary namespace detection works.
     *
     * @return void
     */
    public function testPrimaryNamespace()
    {
        $autoload = [
            'psr-4' => [
                'FOC\\Authenticate' => ''
            ]
        ];
        $this->package->setAutoload($autoload);

        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('FOC\Authenticate', $ns);

        $autoload = [
            'psr-4' => [
                'FOC\Acl\Test' => './tests',
                'FOC\Acl' => ''
            ]
        ];
        $this->package->setAutoload($autoload);
        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('FOC\Acl', $ns);

        $autoload = [
            'psr-4' => [
                'Foo\Bar' => 'foo',
                'Acme\Plugin' => './src'
            ]
        ];
        $this->package->setAutoload($autoload);
        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('Acme\Plugin', $ns);

        $autoload = [
            'psr-4' => [
                'Foo\Bar' => 'bar',
                'Foo\\' => ''
            ]
        ];
        $this->package->setAutoload($autoload);
        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('Foo', $ns);

        $autoload = [
            'psr-4' => [
                'Foo\Bar' => 'bar',
                'Foo' => '.'
            ]
        ];
        $this->package->setAutoload($autoload);
        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('Foo', $ns);

        $autoload = [
            'psr-4' => [
                'Acme\Foo\Bar' => 'bar',
                'Acme\Foo\\' => ''
            ]
        ];
        $this->package->setAutoload($autoload);
        $ns = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('Acme\Foo', $ns);

        $autoload = [
            'psr-4' => [
                'Acme\Foo\Bar' => '',
                'Acme\Foo' => 'src'
            ]
        ];
        $this->package->setAutoload($autoload);
        $name = PluginInstaller::primaryNamespace($this->package);
        $this->assertEquals('Acme\Foo', $name);
    }

    public function testDeterminePlugins()
    {
        $plugin1 = new Package('cakephp/the-thing', '1.0', '1.0');
        $plugin1->setType('cakephp-plugin');
        $plugin1->setAutoload([
            'psr-4' => [
                'TheThing' => 'src/'
            ]
        ]);

        $plugin2 = new Package('cakephp/princess', '1.0', '1.0');
        $plugin2->setType('cakephp-plugin');
        $plugin2->setAutoload([
            'psr-4' => [
                'Princess' => 'src/'
            ]
        ]);

        $packages = [
            $plugin1,
            new Package('SomethingElse', '1.0', '1.0'),
            $plugin2
        ];

        $return = PluginInstaller::determinePlugins(
            $packages,
            $this->path . '/doesnt-exist',
            $this->path . '/vendor'
        );

        $expected = [
            'Princess' => $this->path . '/vendor/cakephp/princess',
            'TheThing' => $this->path . '/vendor/cakephp/the-thing'
        ];
        $this->assertSame($expected, $return, 'Only composer-loaded plugins should be listed');

        $return = PluginInstaller::determinePlugins(
            $packages,
            $this->path . '/plugins',
            $this->path . '/vendor'
        );

        $expected = [
            'Fee' => $this->path . '/plugins/Fee',
            'Foe' => $this->path . '/plugins/Foe',
            'Foo' => $this->path . '/plugins/Foo',
            'Fum' => $this->path . '/plugins/Fum',
            'Princess' => $this->path . '/vendor/cakephp/princess',
            'TheThing' => $this->path . '/vendor/cakephp/the-thing'
        ];
        $this->assertSame($expected, $return, 'Composer and application plugins should be listed');

        $return = PluginInstaller::determinePlugins(
            $packages,
            [$this->path . '/plugins', $this->path . '/app_plugins'],
            $this->path . '/vendor'
        );

        $expected = [
            'Bar' => $this->path . '/app_plugins/Bar',
            'Fee' => $this->path . '/plugins/Fee',
            'Foe' => $this->path . '/plugins/Foe',
            'Foo' => $this->path . '/plugins/Foo',
            'Fum' => $this->path . '/plugins/Fum',
            'Princess' => $this->path . '/vendor/cakephp/princess',
            'TheThing' => $this->path . '/vendor/cakephp/the-thing'
        ];
        $this->assertSame($expected, $return, 'Composer and application plugins should be listed');
    }

    public function testWriteConfigFile()
    {
        $plugins = [
            'Fee' => $this->path . '/plugins/Fee',
            'Foe' => $this->path . '/plugins/Foe',
            'Foo' => $this->path . '/plugins/Foo',
            'Fum' => $this->path . '/plugins/Fum',
            'OddOneOut' => '/some/other/path',
            'Princess' => $this->path . '/vendor/cakephp/princess',
            'TheThing' => $this->path . '/vendor/cakephp/the-thing',
            'Vendor\Plugin' => $this->path . '/vendor/vendor/plugin'
        ];

        $path = $this->path . '/vendor/cakephp-plugins.php';
        PluginInstaller::writeConfigFile($path, $plugins);

        $this->assertFileExists($path);
        $contents = file_get_contents($path);

        $this->assertContains('<?php', $contents);
        $this->assertContains('$baseDir = dirname(dirname(__FILE__));', $contents);
        $this->assertContains(
            "'Fee' => \$baseDir . '/plugins/Fee/'",
            $contents,
            'paths should be relative for app-plugins'
        );
        $this->assertContains(
            "'Princess' => \$baseDir . '/vendor/cakephp/princess/'",
            $contents,
            'paths should be relative for vendor-plugins'
        );
        $this->assertContains(
            "'OddOneOut' => '/some/other/path/'",
            $contents,
            'paths should stay absolute if it\'s not under the application root'
        );
        $this->assertContains(
            "'Vendor/Plugin' => \$baseDir . '/vendor/vendor/plugin/'",
            $contents,
            'Plugin namespaces should use forward slash'
        );

        // Ensure all plugin paths are slash terminated
        foreach ($plugins as &$plugin) {
            $plugin .= '/';
        }
        unset($plugin);

        $result = require $path;
        $expected = [
            'plugins' => $plugins
        ];
        $expected['plugins']['Vendor/Plugin'] = $expected['plugins']['Vendor\Plugin'];
        unset($expected['plugins']['Vendor\Plugin']);
        $this->assertSame($expected, $result, 'The evaluated result should be the same as the input except for namespaced plugin');
    }

    public function testUpdateConfigNoConfigFile()
    {
        $this->installer->updateConfig('DebugKit', '/vendor/cakephp/DebugKit');
        $this->assertFileExists($this->path . '/vendor/cakephp-plugins.php');
        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains("'plugins' =>", $contents);
        $this->assertContains("'DebugKit' => '/vendor/cakephp/DebugKit/'", $contents);
    }

    public function testUpdateConfigAddPathInvalidFile()
    {
        file_put_contents($this->path . '/vendor/cakephp-plugins.php', '<?php $foo = "DERP";');

        $this->io->expects($this->once())
            ->method('write');
        $this->installer->updateConfig('DebugKit', '/vendor/cakephp/DebugKit');
    }

    public function testUpdateConfigAddPathFileExists()
    {
        file_put_contents(
            $this->path . '/vendor/cakephp-plugins.php',
            '<?php $config = ["plugins" => ["Bake" => "/some/path"]];'
        );

        $this->installer->updateConfig('DebugKit', '/vendor/cakephp/DebugKit');
        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains("'plugins' =>", $contents);
        $this->assertContains("'DebugKit' => '/vendor/cakephp/DebugKit/'", $contents);
        $this->assertContains("'Bake' => '/some/path/'", $contents);
    }

    /**
     * testUpdateConfigAddRootPath
     *
     * @return void
     */
    public function testUpdateConfigAddRootPath()
    {
        file_put_contents(
            $this->path . '/vendor/cakephp-plugins.php',
            '<?php return ["plugins" => ["Bake" => "/some/path"]];'
        );

        $this->installer->updateConfig('DebugKit', $this->path . '/vendor/cakephp/debugkit');
        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains('$baseDir = dirname(dirname(__FILE__));', $contents);
        $this->assertContains("'DebugKit' => \$baseDir . '/vendor/cakephp/debugkit/'", $contents);
        $this->assertContains("'Bake' => '/some/path/'", $contents);
    }

    /**
     * testUpdateConfigAddPath
     *
     * @return void
     */
    public function testUpdateConfigAddPath()
    {
        file_put_contents(
            $this->path . '/vendor/cakephp-plugins.php',
            '<?php return ["plugins" => ["Bake" => "/some/path"]];'
        );

        $this->installer->updateConfig('DebugKit', '/vendor/cakephp/debugkit');
        $this->installer->updateConfig('ADmad\JwtAuth', '/vendor/admad/cakephp-jwt-auth');

        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains("'DebugKit' => '/vendor/cakephp/debugkit/'", $contents);
        $this->assertContains("'Bake' => '/some/path/'", $contents);
        $this->assertContains("'ADmad/JwtAuth' => '/vendor/admad/cakephp-jwt-auth/'", $contents);
    }

    /**
     * test adding windows paths.
     *
     * @return void
     */
    public function testUpdateConfigAddPathWindows()
    {
        file_put_contents(
            $this->path . '/vendor/cakephp-plugins.php',
            '<?php return ["plugins" => ["Bake" => "/some/path"]];'
        );

        $this->installer->updateConfig('DebugKit', '\vendor\cakephp\debugkit');

        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains("'DebugKit' => '/vendor/cakephp/debugkit/'", $contents);
    }

    /**
     * testUpdateConfigRemovePath
     *
     * @return void
     */
    public function testUpdateConfigRemovePath()
    {
        file_put_contents(
            $this->path . '/vendor/cakephp-plugins.php',
            '<?php $config = ["plugins" => ["Bake" => "/some/path"]];'
        );

        $this->installer->updateConfig('Bake', '');
        $contents = file_get_contents($this->path . '/vendor/cakephp-plugins.php');
        $this->assertContains('<?php', $contents);
        $this->assertContains("'plugins' =>", $contents);
        $this->assertNotContains("Bake", $contents);
    }
}
