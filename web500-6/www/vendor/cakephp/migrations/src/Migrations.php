<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Migrations;

use Cake\Datasource\ConnectionManager;
use Phinx\Config\Config;
use Phinx\Config\ConfigInterface;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * The Migrations class is responsible for handling migrations command
 * within an none-shell application.
 */
class Migrations
{

    use ConfigurationTrait;

    /**
     * The OutputInterface.
     * Should be a \Symfony\Component\Console\Output\NullOutput instance
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * CakeManager instance
     *
     * @var \Migrations\CakeManager
     */
    protected $manager;

    /**
     * Default options to use
     *
     * @var array
     */
    protected $default = [];

    /**
     * Current command being run.
     * Useful if some logic needs to be applied in the ConfigurationTrait depending
     * on the command
     *
     * @var array
     */
    protected $command;

    /**
     * Stub input to feed the manager class since we might not have an input ready when we get the Manager using
     * the `getManager()` method
     *
     * @var \Symfony\Component\Console\Input\ArrayInput
     */
    protected $stubInput;

    /**
     * Constructor
     * @param array $default Default option to be used when calling a method.
     * Available options are :
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     */
    public function __construct(array $default = [])
    {
        $this->output = new NullOutput();
        $this->stubInput = new ArrayInput([]);

        if ($default) {
            $this->default = $default;
        }
    }

    /**
     * Sets the command
     *
     * @param string $command Command name to store.
     * @return self
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Sets the input object that should be used for the command class. This object
     * is used to inspect the extra options that are needed for CakePHP apps.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input the input object
     * @return void
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Gets the command
     *
     * @return string Command name
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns the status of each migrations based on the options passed
     *
     * @param array $options Options to pass to the command
     * Available options are :
     *
     * - `format` Format to output the response. Can be 'json'
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     *
     * @return array The migrations list and their statuses
     */
    public function status($options = [])
    {
        $this->setCommand('status');
        $input = $this->getInput('Status', [], $options);
        $params = ['default', $input->getOption('format')];

        return $this->run('printStatus', $params, $input);
    }

    /**
     * Migrates available migrations
     *
     * @param array $options Options to pass to the command
     * Available options are :
     *
     * - `target` The version number to migrate to. If not provided, will migrate
     * everything it can
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     * - `date` The date to migrate to
     *
     * @return bool Success
     */
    public function migrate($options = [])
    {
        $this->setCommand('migrate');
        $input = $this->getInput('Migrate', [], $options);
        $method = 'migrate';
        $params = ['default', $input->getOption('target')];

        if ($input->getOption('date')) {
            $method = 'migrateToDateTime';
            $params[1] = new \DateTime($input->getOption('date'));
        }

        $this->run($method, $params, $input);

        return true;
    }

    /**
     * Rollbacks migrations
     *
     * @param array $options Options to pass to the command
     * Available options are :
     *
     * - `target` The version number to migrate to. If not provided, will only migrate
     * the last migrations registered in the phinx log
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     * - `date` The date to rollback to
     *
     * @return bool Success
     */
    public function rollback($options = [])
    {
        $this->setCommand('rollback');
        $input = $this->getInput('Rollback', [], $options);
        $method = 'rollback';
        $params = ['default', $input->getOption('target')];

        if ($input->getOption('date')) {
            $method = 'rollbackToDateTime';
            $params[1] = new \DateTime($input->getOption('date'));
        }

        $this->run($method, $params, $input);

        return true;
    }

    /**
     * Marks a migration as migrated
     *
     * @param int $version The version number of the migration to mark as migrated
     * @param array $options Options to pass to the command
     * Available options are :
     *
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     *
     * @return bool Success
     */
    public function markMigrated($version = null, $options = [])
    {
        $this->setCommand('mark_migrated');

        if (isset($options['target']) &&
            isset($options['exclude']) &&
            isset($options['only'])
        ) {
            $exceptionMessage = 'You should use `exclude` OR `only` (not both) along with a `target` argument';
            throw new \InvalidArgumentException($exceptionMessage);
        }

        $input = $this->getInput('MarkMigrated', ['version' => $version], $options);
        $this->setInput($input);

        $migrationPaths = $this->getConfig()->getMigrationPaths();
        $params = [
            array_pop($migrationPaths),
            $this->getManager()->getVersionsToMark($input),
            $this->output
        ];

        $this->run('markVersionsAsMigrated', $params, $input);

        return true;
    }

    /**
     * Seed the database using a seed file
     *
     * @param array $options Options to pass to the command
     * Available options are :
     *
     * - `connection` The datasource connection to use
     * - `source` The folder where migrations are in
     * - `plugin` The plugin containing the migrations
     * - `seed` The seed file to use
     *
     * @return bool Success
     */
    public function seed($options = [])
    {
        $this->setCommand('seed');
        $input = $this->getInput('Seed', [], $options);

        $seed = $input->getOption('seed');
        if (!$seed) {
            $seed = null;
        }

        $params = ['default', $seed];
        $this->run('seed', $params, $input);

        return true;
    }

    /**
     * Runs the method needed to execute and return
     *
     * @param string $method Manager method to call
     * @param array $params Manager params to pass
     * @param \Symfony\Component\Console\Input\InputInterface $input InputInterface needed for the
     * Manager to properly run
     *
     * @return mixed The result of the CakeManager::$method() call
     */
    protected function run($method, $params, $input)
    {
        if ($this->configuration instanceof Config) {
            $migrationPaths = $this->getConfig()->getMigrationPaths();
            $migrationPath = array_pop($migrationPaths);
            $seedPaths = $this->getConfig()->getSeedPaths();
            $seedPath = array_pop($seedPaths);
        }

        if ($this->manager instanceof Manager) {
            $pdo = $this->manager->getEnvironment('default')
                ->getAdapter()
                ->getConnection();
        }

        $this->setInput($input);
        $newConfig = $this->getConfig(true);
        $manager = $this->getManager($newConfig);
        $manager->setInput($input);

        if (isset($pdo)) {
            $this->manager->getEnvironment('default')
                ->getAdapter()
                ->setConnection($pdo);
        }

        $newMigrationPaths = $newConfig->getMigrationPaths();
        if (isset($migrationPath) && array_pop($newMigrationPaths) !== $migrationPath) {
            $manager->resetMigrations();
        }
        $newSeedPaths = $newConfig->getSeedPaths();
        if (isset($seedPath) && array_pop($newSeedPaths) !== $seedPath) {
            $manager->resetSeeds();
        }

        return call_user_func_array([$manager, $method], $params);
    }

    /**
     * Returns an instance of CakeManager
     *
     * @param \Phinx\Config\ConfigInterface|null $config ConfigInterface the Manager needs to run
     * @return \Migrations\CakeManager Instance of CakeManager
     */
    public function getManager($config = null)
    {
        if (!($this->manager instanceof CakeManager)) {
            if (!($config instanceof ConfigInterface)) {
                throw new \RuntimeException(
                    'You need to pass a ConfigInterface object for your first getManager() call'
                );
            }

            $input = $this->input ?: $this->stubInput;
            $this->manager = new CakeManager($config, $input, $this->output);
        } elseif ($config !== null) {
            $defaultEnvironment = $config->getEnvironment('default');
            try {
                $environment = $this->manager->getEnvironment('default');
                $oldConfig = $environment->getOptions();
                unset($oldConfig['connection']);
                if ($oldConfig == $defaultEnvironment) {
                    $defaultEnvironment['connection'] = $environment
                        ->getAdapter()
                        ->getConnection();
                }
            } catch (\InvalidArgumentException $e) {
            }
            $config['environments'] = ['default' => $defaultEnvironment];
            $this->manager->setEnvironments([]);
            $this->manager->setConfig($config);
        }

        $this->setAdapter();

        return $this->manager;
    }

    /**
     * Sets the adapter the manager is going to need to operate on the DB
     * This will make sure the adapter instance is a \Migrations\CakeAdapter instance
     *
     * @return void
     */
    public function setAdapter()
    {
        if ($this->input !== null) {
            $connectionName = 'default';
            if ($this->input->getOption('connection')) {
                $connectionName = $this->input->getOption('connection');
            }
            $connection = ConnectionManager::get($connectionName);

            $env = $this->manager->getEnvironment('default');
            $adapter = $env->getAdapter();
            if (!$adapter instanceof CakeAdapter) {
                $env->setAdapter(new CakeAdapter($adapter, $connection));
            }
        }
    }

    /**
     * Get the input needed for each commands to be run
     *
     * @param string $command Command name for which we need the InputInterface
     * @param array $arguments Simple key/values array representing the command arguments
     * to pass to the InputInterface
     * @param array $options Simple key/values array representing the command options
     * to pass to the InputInterface
     * @return \Symfony\Component\Console\Input\InputInterface InputInterface needed for the
     * Manager to properly run
     */
    public function getInput($command, $arguments, $options)
    {
        $className = '\Migrations\Command\\' . $command;
        $options = $arguments + $this->prepareOptions($options);
        $definition = (new $className())->getDefinition();

        return new ArrayInput($options, $definition);
    }

    /**
     * Prepares the option to pass on to the InputInterface
     *
     * @param array $options Simple key-values array to pass to the InputInterface
     * @return array Prepared $options
     */
    protected function prepareOptions($options = [])
    {
        $options = array_merge($this->default, $options);
        if (!$options) {
            return $options;
        }

        foreach ($options as $name => $value) {
            $options['--' . $name] = $value;
            unset($options[$name]);
        }

        return $options;
    }
}
