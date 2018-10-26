<?php

namespace Cake\Test\Composer\Installer;

use Cake\Composer\Installer\PluginInstaller as PluginInstallerSrc;

/**
 * Test double for static methods in PluginInstaller
 */
class PluginInstaller extends PluginInstallerSrc
{

    /**
     * Overriden to return a test-config file
     *
     * @param string $vendorDir
     * @return string path to test plugins config file
     */
    public static function configFile($vendorDir)
    {
        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'plugin-installer-test';

        return $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'cakephp-plugins.php';
    }
}
