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
namespace Migrations\Shell;

use Cake\Console\Shell;
use Migrations\MigrationsDispatcher;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * A wrapper shell for phinx migrations, used to inject our own
 * console actions so that database configuration already defined
 * for the application can be reused.
 *
 * @property \Migrations\Shell\Task\CreateTask $Create
 * @property \Migrations\Shell\Task\DumpTask $Dump
 * @property \Migrations\Shell\Task\MarkMigratedTask $MarkMigrated
 * @property \Migrations\Shell\Task\MigrateTask $Migrate
 * @property \Migrations\Shell\Task\RollbackTask $Rollback
 * @property \Migrations\Shell\Task\StatusTask $Status
 */
class MigrationsShell extends Shell
{

    /**
     * {@inheritDoc}
     */
    public $tasks = [
        'Migrations.Create',
        'Migrations.Dump',
        'Migrations.MarkMigrated',
        'Migrations.Migrate',
        'Migrations.Rollback',
        'Migrations.Status'
    ];

    /**
     * Array of arguments to run the shell with.
     *
     * @var array
     */
    public $argv = [];

    /**
     * Defines what options can be passed to the shell.
     * This is required because CakePHP validates the passed options
     * and would complain if something not configured here is present
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        return parent::getOptionParser()
            ->addOption('plugin', ['short' => 'p'])
            ->addOption('target', ['short' => 't'])
            ->addOption('connection', ['short' => 'c'])
            ->addOption('source', ['short' => 's'])
            ->addOption('seed')
            ->addOption('ansi')
            ->addOption('no-ansi')
            ->addOption('no-lock', ['boolean' => true])
            ->addOption('force', ['boolean' => true])
            ->addOption('version', ['short' => 'V'])
            ->addOption('no-interaction', ['short' => 'n'])
            ->addOption('template', ['short' => 't'])
            ->addOption('format', ['short' => 'f'])
            ->addOption('only', ['short' => 'o'])
            ->addOption('exclude', ['short' => 'x']);
    }

    /**
     * Defines constants that are required by phinx to get running
     *
     * @return void
     */
    public function initialize()
    {
        if (!defined('PHINX_VERSION')) {
            define('PHINX_VERSION', (0 === strpos('@PHINX_VERSION@', '@PHINX_VERSION')) ? '0.4.3' : '@PHINX_VERSION@');
        }
        parent::initialize();
    }

    /**
     * This acts as a front-controller for phinx. It just instantiates the classes
     * responsible for parsing the command line from phinx and gives full control of
     * the rest of the flow to it.
     *
     * The input parameter of the ``MigrationDispatcher::run()`` method is manually built
     * in case a MigrationsShell is dispatched using ``Shell::dispatch()``.
     *
     * @return bool Success of the call.
     */
    public function main()
    {
        $app = $this->getApp();
        $input = new ArgvInput($this->argv);
        $app->setAutoExit(false);
        $exitCode = $app->run($input, $this->getOutput());

        if (isset($this->argv[1]) && in_array($this->argv[1], ['migrate', 'rollback']) &&
            !$this->params['no-lock'] &&
            $exitCode === 0
        ) {
            $dispatchCommand = 'migrations dump';
            if (!empty($this->params['connection'])) {
                $dispatchCommand .= ' -c ' . $this->params['connection'];
            }

            if (!empty($this->params['plugin'])) {
                $dispatchCommand .= ' -p ' . $this->params['plugin'];
            }

            $dumpExitCode = $this->dispatchShell($dispatchCommand);
        }

        if (isset($dumpExitCode) && $exitCode === 0 && $dumpExitCode !== 0) {
            $exitCode = 1;
        }

        return $exitCode === 0;
    }

    /**
     * Returns the MigrationsDispatcher the Shell will have to use
     *
     * @return \Migrations\MigrationsDispatcher
     */
    protected function getApp()
    {
        return new MigrationsDispatcher(PHINX_VERSION);
    }

    /**
     * Returns the instance of OutputInterface the MigrationsDispatcher will have to use.
     *
     * @return \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected function getOutput()
    {
        return new ConsoleOutput();
    }

    /**
     * Override the default behavior to save the command called
     * in order to pass it to the command dispatcher
     *
     * {@inheritDoc}
     */
    public function runCommand($argv, $autoMethod = false, $extra = [])
    {
        array_unshift($argv, 'migrations');
        $this->argv = $argv;

        return parent::runCommand($argv, $autoMethod, $extra);
    }

    /**
     * Display the help in the correct format
     *
     * @param string $command The command to get help for.
     * @return int|bool|null Exit code or number of bytes written to stdout
     */
    protected function displayHelp($command)
    {
        return $this->main();
    }

    /**
     * {@inheritDoc}
     */
    // @codingStandardsIgnoreStart
    protected function _displayHelp($command)
    {
        // @codingStandardsIgnoreEnd
        return $this->displayHelp($command);
    }
}
