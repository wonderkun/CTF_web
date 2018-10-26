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

use Cake\Utility\Inflector;
use Migrations\ConfigurationTrait;
use Phinx\Console\Command\Create as CreateCommand;
use Phinx\Util\Util;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends CreateCommand
{

    use CommandTrait;
    use ConfigurationTrait {
        execute as parentExecute;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Create a new migration')
            ->addArgument('name', InputArgument::REQUIRED, 'What is the name of the migration?')
            ->setHelp(sprintf(
                '%sCreates a new database migration file%s',
                PHP_EOL,
                PHP_EOL
            ))
            ->addOption('plugin', 'p', InputOption::VALUE_REQUIRED, 'The plugin the file should be created for')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The datasource connection to use')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'The folder where migrations are in')
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Use an alternative template')
            ->addOption(
                'class',
                'l',
                InputOption::VALUE_REQUIRED,
                'Use a class implementing "' . parent::CREATION_INTERFACE . '" to generate the template'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Specify the path in which to create this migration'
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
        $this->parentExecute($input, $output);

        $output->writeln('<info>renaming file in CamelCase to follow CakePHP convention...</info>');

        $migrationPaths = $this->getConfig()->getMigrationPaths();
        $migrationPath = array_pop($migrationPaths) . DS;
        $name = $input->getArgument('name');
        list($phinxTimestamp, $phinxName) = explode('_', Util::mapClassNameToFileName($name), 2);
        $migrationFilename = glob($migrationPath . '*' . $phinxName);

        if (empty($migrationFilename)) {
            $output->writeln(sprintf('<info>An error occurred while renaming file</info>'));
        } else {
            $migrationFilename = $migrationFilename[0];
            $path = dirname($migrationFilename) . DS;
            $name = Inflector::camelize($name);
            $newPath = $path . Util::getCurrentTimestamp() . '_' . $name . '.php';

            $output->writeln('<info>renaming file in CamelCase to follow CakePHP convention...</info>');
            if (rename($migrationFilename, $newPath)) {
                $output->writeln(sprintf('<info>File successfully renamed to %s</info>', $newPath));
            } else {
                $output->writeln(sprintf('<info>An error occurred while renaming file to %s</info>', $newPath));
            }
        }
    }
}
