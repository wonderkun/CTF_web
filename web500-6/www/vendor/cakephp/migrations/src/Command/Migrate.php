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
namespace Migrations\Command;

use Cake\Event\EventDispatcherTrait;
use Migrations\ConfigurationTrait;
use Phinx\Console\Command\Migrate as MigrateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends MigrateCommand
{

    use CommandTrait;
    use ConfigurationTrait {
        execute as parentExecute;
    }
    use EventDispatcherTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('Migrate the database')
            ->setHelp('runs all available migrations, optionally up to a specific version')
            ->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to migrate to')
            ->addOption('--date', '-d', InputOption::VALUE_REQUIRED, 'The date to migrate to')
            ->addOption('--plugin', '-p', InputOption::VALUE_REQUIRED, 'The plugin containing the migrations')
            ->addOption('--connection', '-c', InputOption::VALUE_REQUIRED, 'The datasource connection to use')
            ->addOption('--source', '-s', InputOption::VALUE_REQUIRED, 'The folder where migrations are in')
            ->addOption(
                '--no-lock',
                null,
                InputOption::VALUE_NONE,
                'If present, no lock file will be generated after migrating'
            );
    }

    /**
     * Overrides the action execute method in order to vanish the idea of environments
     * from phinx. CakePHP does not believe in the idea of having in-app environments
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input the input object
     * @param \Symfony\Component\Console\Output\OutputInterface $output the output object
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->dispatchEvent('Migration.beforeMigrate');
        if ($event->isStopped()) {
            return $event->result;
        }
        $this->parentExecute($input, $output);
        $this->dispatchEvent('Migration.afterMigrate');
    }
}
