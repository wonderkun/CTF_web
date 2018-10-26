<?php
namespace Cake\Composer\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use RuntimeException;

class PluginInstaller extends LibraryInstaller
{
    /**
     * A flag to check usage - once
     *
     * @var bool
     */
    protected static $checkUsage = true;

    /**
     * Check usage upon construction
     *
     * @param IOInterface $io composer object
     * @param Composer    $composer composer object
     * @param string      $type what are we loading
     * @param Filesystem  $filesystem composer object
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library', Filesystem $filesystem = null)
    {
        parent::__construct($io, $composer, $type, $filesystem);
        $this->checkUsage($composer);
    }

    /**
     * Check that the root composer.json file use the post-autoload-dump hook
     *
     * If not, warn the user they need to update their application's composer file.
     * Do nothing if the main project is not a project (if it's a plugin in development).
     *
     * @param Composer $composer object
     * @return void
     */
    public function checkUsage(Composer $composer)
    {
        if (static::$checkUsage === false) {
            return;
        }
        static::$checkUsage = false;

        $package = $composer->getPackage();

        if (!$package || $package->getType() !== 'project') {
            return;
        }

        $scripts = $composer->getPackage()->getScripts();
        $postAutoloadDump = 'Cake\Composer\Installer\PluginInstaller::postAutoloadDump';
        if (!isset($scripts['post-autoload-dump']) ||
            !in_array($postAutoloadDump, $scripts['post-autoload-dump'])
        ) {
            $this->warnUser(
                'Action required!',
                'The CakePHP plugin installer has been changed, please update your' .
                ' application composer.json file to add the post-autoload-dump hook.' .
                ' See the changes in https://github.com/cakephp/app/pull/216 for more' .
                ' info.'
            );
        }
    }

    /**
     * Warn the developer of action they need to take
     *
     * @param string $title Warning title
     * @param string $text warning text
     *
     * @return void
     */
    public function warnUser($title, $text)
    {
        $wrap = function ($text, $width = 75) {
            return '<error>     ' . str_pad($text, $width) . '</error>';
        };

        $messages = [
            '',
            '',
            $wrap(''),
            $wrap($title),
            $wrap(''),
        ];

        $lines = explode("\n", wordwrap($text, 68));
        foreach ($lines as $line) {
            $messages[] = $wrap($line);
        }

        $messages = array_merge($messages, [$wrap(''), '', '']);

        $this->io->write($messages);
    }

    /**
     * Called whenever composer (re)generates the autoloader
     *
     * Recreates CakePHP's plugin path map, based on composer information
     * and available app-plugins.
     *
     * @param Event $event the composer event object
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $config = $composer->getConfig();

        $vendorDir = realpath($config->get('vendor-dir'));

        $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();
        $extra = $event->getComposer()->getPackage()->getExtra();
        if (empty($extra['plugin-paths'])) {
            $pluginsDir = dirname($vendorDir) . DIRECTORY_SEPARATOR . 'plugins';
        } else {
            $pluginsDir = $extra['plugin-paths'];
        }

        $plugins = static::determinePlugins($packages, $pluginsDir, $vendorDir);

        $configFile = static::_configFile($vendorDir);
        static::writeConfigFile($configFile, $plugins);
    }

    /**
     * Find all plugins available
     *
     * Add all composer packages of type cakephp-plugin, and all plugins located
     * in the plugins directory to a plugin-name indexed array of paths
     *
     * @param array $packages an array of \Composer\Package\PackageInterface objects
     * @param string|array $pluginsDir the path to the plugins dir
     * @param string $vendorDir the path to the vendor dir
     * @return array plugin-name indexed paths to plugins
     */
    public static function determinePlugins($packages, $pluginsDir = 'plugins', $vendorDir = 'vendor')
    {
        $plugins = [];

        foreach ($packages as $package) {
            if ($package->getType() !== 'cakephp-plugin') {
                continue;
            }

            $ns = static::primaryNamespace($package);
            $path = $vendorDir . DIRECTORY_SEPARATOR . $package->getPrettyName();
            $plugins[$ns] = $path;
        }

        foreach ((array)$pluginsDir as $path) {
            $path = static::fullpath($path, $vendorDir);
            if (is_dir($path)) {
                $dir = new \DirectoryIterator($path);
                foreach ($dir as $info) {
                    if (!$info->isDir() || $info->isDot()) {
                        continue;
                    }

                    $name = $info->getFilename();
                    if ($name{0} === '.') {
                        continue;
                    }

                    $plugins[$name] = $path . DIRECTORY_SEPARATOR . $name;
                }
            }
        }

        ksort($plugins);

        return $plugins;
    }

    /**
     * Turns relative paths in full paths.
     *
     * @param string $path Path
     * @param string $vendorDir The path to the vendor dir
     * @return string
     */
    protected static function fullpath($path, $vendorDir)
    {
        if (preg_match('{^(?:/|[a-z]:|[a-z0-9.]+://)}i', $path)) {
            return rtrim($path, '/');
        }

        if (substr($path, 0, 2) === './') {
            $path = substr($path, 2);
        }

        return rtrim(dirname($vendorDir) . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Rewrite the config file with a complete list of plugins
     *
     * @param string $configFile the path to the config file
     * @param array $plugins of plugins
     * @param string|null $root The root directory. Defaults to a value generated from $configFile
     * @return void
     */
    public static function writeConfigFile($configFile, $plugins, $root = null)
    {
        $root = $root ?: dirname(dirname($configFile));

        $data = [];
        foreach ($plugins as $name => $pluginPath) {
            // Normalize to *nix paths.
            $pluginPath = str_replace('\\', '/', $pluginPath);
            $pluginPath .= '/';

            $pluginPath = str_replace(
                DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $pluginPath
            );

            // Namespaced plugins should use /
            $name = str_replace('\\', '/', $name);

            $data[] = sprintf("        '%s' => '%s'", $name, $pluginPath);
        }

        $data = implode(",\n", $data);

        $contents = <<<'PHP'
<?php
$baseDir = dirname(dirname(__file__));
return [
    'plugins' => [
%s
    ]
];
PHP;
        $contents = sprintf($contents, $data);

        // Gross hacks to work around composer smashing `__FILE__` in this
        // PHP file when it runs the code through eval()
        $uppercase = function ($matches) {
            return strtoupper($matches[0]);
        };
        $contents = preg_replace_callback('/__file__/', $uppercase, $contents);

        $root = str_replace(
            DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $root
        );

        // Normalize to *nix paths.
        $root = str_replace('\\', '/', $root);
        $contents = str_replace('\'' . $root, '$baseDir . \'', $contents);

        file_put_contents($configFile, $contents);
    }

    /**
     * Path to the plugin config file
     *
     * @param string $vendorDir path to composer-vendor dir
     * @return string absolute file path
     */
    protected static function _configFile($vendorDir)
    {
        return $vendorDir . DIRECTORY_SEPARATOR . 'cakephp-plugins.php';
    }

    /**
     * Get the primary namespace for a plugin package.
     *
     * @param \Composer\Package\PackageInterface $package composer object
     * @return string The package's primary namespace.
     * @throws \RuntimeException When the package's primary namespace cannot be determined.
     */
    public static function primaryNamespace($package)
    {
        $primaryNs = null;
        $autoLoad = $package->getAutoload();
        foreach ($autoLoad as $type => $pathMap) {
            if ($type !== 'psr-4') {
                continue;
            }
            $count = count($pathMap);

            if ($count === 1) {
                $primaryNs = key($pathMap);
                break;
            }

            $matches = preg_grep('#^(\./)?src/?$#', $pathMap);
            if ($matches) {
                $primaryNs = key($matches);
                break;
            }

            foreach (['', '.'] as $path) {
                $key = array_search($path, $pathMap, true);
                if ($key !== false) {
                    $primaryNs = $key;
                }
            }
            break;
        }

        if (!$primaryNs) {
            throw new RuntimeException(
                sprintf(
                    "Unable to get primary namespace for package %s." .
                    "\nEnsure you have added proper 'autoload' section to your plugin's config" .
                    " as stated in README on https://github.com/cakephp/plugin-installer",
                    $package->getName()
                )
            );
        }

        return trim($primaryNs, '\\');
    }

    /**
     * Decides if the installer supports the given type.
     *
     * This installer only supports package of type 'cakephp-plugin'.
     *
     * @return bool
     */
    public function supports($packageType)
    {
        return 'cakephp-plugin' === $packageType;
    }

    /**
     * Installs specific plugin.
     *
     * After the plugin is installed, app's `cakephp-plugins.php` config file is updated with
     * plugin namespace to path mapping.
     *
     * @param \Composer\Repository\InstalledRepositoryInterface $repo Repository in which to check.
     * @param \Composer\Package\PackageInterface $package Package instance.
     * @deprecated superceeded by the post-autoload-dump hook
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $path = $this->getInstallPath($package);
        $ns = static::primaryNamespace($package);
        $this->updateConfig($ns, $path);
    }

    /**
     * Updates specific plugin.
     *
     * After the plugin is installed, app's `cakephp-plugins.php` config file is updated with
     * plugin namespace to path mapping.
     *
     * @param \Composer\Repository\InstalledRepositoryInterface $repo Repository in which to check.
     * @param \Composer\Package\PackageInterface $initial Already installed package version.
     * @param \Composer\Package\PackageInterface $target Updated version.
     * @deprecated superceeded by the post-autoload-dump hook
     *
     * @throws \InvalidArgumentException if $initial package is not installed
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);

        $ns = static::primaryNamespace($initial);
        $this->updateConfig($ns, null);

        $path = $this->getInstallPath($target);
        $ns = static::primaryNamespace($target);
        $this->updateConfig($ns, $path);
    }

    /**
     * Uninstalls specific package.
     *
     * @param \Composer\Repository\InstalledRepositoryInterface $repo Repository in which to check.
     * @param \Composer\Package\PackageInterface $package Package instance.
     * @deprecated superceeded by the post-autoload-dump hook
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);
        $path = $this->getInstallPath($package);
        $ns = static::primaryNamespace($package);
        $this->updateConfig($ns, null);
    }

    /**
     * Update the plugin path for a given package.
     *
     * @param string $name The plugin name being installed.
     * @param string $path The path, the plugin is being installed into.
     */
    public function updateConfig($name, $path)
    {
        $name = str_replace('\\', '/', $name);
        $configFile = static::_configFile($this->vendorDir);
        $this->_ensureConfigFile($configFile);

        $return = include $configFile;
        if (is_array($return) && empty($config)) {
            $config = $return;
        }
        if (!isset($config)) {
            $this->io->write(
                'ERROR - `vendor/cakephp-plugins.php` file is invalid. ' .
                'Plugin path configuration not updated.'
            );

            return;
        }
        if (!isset($config['plugins'])) {
            $config['plugins'] = [];
        }
        if ($path == null) {
            unset($config['plugins'][$name]);
        } else {
            $config['plugins'][$name] = $path;
        }
        $root = dirname($this->vendorDir);
        static::writeConfigFile($configFile, $config['plugins'], $root);
    }

    /**
     * Ensure that the vendor/cakephp-plugins.php file exists.
     *
     * If config/plugins.php is found - copy it to the vendor folder
     *
     * @param string $path the config file path.
     * @return void
     */
    protected function _ensureConfigFile($path)
    {
        if (file_exists($path)) {
            if ($this->io->isVerbose()) {
                $this->io->write('vendor/cakephp-plugins.php exists.');
            }

            return;
        }

        $oldPath = dirname(dirname($path)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'plugins.php';
        if (file_exists($oldPath)) {
            copy($oldPath, $path);
            if ($this->io->isVerbose()) {
                $this->io->write('config/plugins.php found and copied to vendor/cakephp-plugins.php.');
            }

            return;
        }

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path));
        }
        $root = dirname($this->vendorDir);
        static::writeConfigFile($path, [], $root);

        if ($this->io->isVerbose()) {
            $this->io->write('Created vendor/cakephp-plugins.php');
        }
    }
}
