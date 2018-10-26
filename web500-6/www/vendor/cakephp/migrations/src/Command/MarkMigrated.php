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

use InvalidArgumentException;
use Migrations\ConfigurationTrait;
use Phinx\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MarkMigrated extends AbstractCommand
{

    use CommandTrait;
    use ConfigurationTrait;

    /**
     * The console output instance
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output object.
     * @return mixed
     */
    public function output(OutputInterface $output = null)
    {
        if ($output !== null) {
            $this->output = $output;
        }

        return $this->output;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mark_migrated')
            ->setDescription('Mark a migration as migrated')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'DEPRECATED: use `bin/cake migrations mark_migrated --target=VERSION --only` instead'
            )
            ->setHelp(sprintf(
                '%sMark migrations as migrated%s',
                PHP_EOL,
                PHP_EOL
            ))
            ->addOption('plugin', 'p', InputOption::VALUE_REQUIRED, 'The plugin the file should be created for')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The datasource connection to use')
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'The folder where migrations are in')
            ->addOption(
                'target',
                't',
                InputOption::VALUE_REQUIRED,
                'It will mark migrations from beginning to the given version'
            )
            ->addOption(
                'exclude',
                'x',
                InputOption::VALUE_NONE,
                'If present it will mark migrations from beginning until the given version, excluding it'
            )
            ->addOption(
                'only',
                'o',
                InputOption::VALUE_NONE,
                'If present it will only mark the given migration version'
            );
    }

    /**
     * Mark migrations as migrated
     *
     * `bin/cake migrations mark_migrated` mark every migrations as migrated
     * `bin/cake migrations mark_migrated all` DEPRECATED: the same effect as above
     * `bin/cake migrations mark_migrated --target=VERSION` mark migrations as migrated up to the VERSION param
     * `bin/cake migrations mark_migrated --target=20150417223600 --exclude` mark migrations as migrated up to
     *  and except the VERSION param
     * `bin/cake migrations mark_migrated --target=20150417223600 --only` mark only the VERSION migration as migrated
     * `bin/cake migrations mark_migrated 20150417223600` DEPRECATED: the same effect as above
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input the input object
     * @param \Symfony\Component\Console\Output\OutputInterface $output the output object
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInput($input);
        $this->bootstrap($input, $output);
        $this->output($output);

        $migrationPaths = $this->getConfig()->getMigrationPaths();
        $path = array_pop($migrationPaths);

        if ($this->invalidOnlyOrExclude()) {
            $output->writeln(
                "<error>You should use `--exclude` OR `--only` (not both) along with a `--target` !</error>"
            );

            return;
        }

        if ($this->isUsingDeprecatedAll()) {
            $this->outputDeprecatedAllMessage();
        }

        if ($this->isUsingDeprecatedVersion()) {
            $this->outputDeprecatedVersionMessage();
        }

        try {
            $versions = $this->getManager()->getVersionsToMark($input);
        } catch (InvalidArgumentException $e) {
            $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));

            return;
        }

        $this->getManager()->markVersionsAsMigrated($path, $versions, $output);
    }

    /**
     * Checks if the version is using the deprecated `all`
     *
     * @return bool Returns true if it is using the deprecated `all` otherwise false
     */
    protected function isUsingDeprecatedAll()
    {
        $version = $this->input->getArgument('version');

        return $version === 'all' || $version === '*';
    }

    /**
     * Checks if the input has the `--exclude` option
     *
     * @return bool Returns true if `--exclude` option gets passed in otherwise false
     */
    protected function hasExclude()
    {
        return $this->input->getOption('exclude');
    }

    /**
     * Checks if the input has the `--only` option
     *
     * @return bool Returns true if `--only` option gets passed in otherwise false
     */
    protected function hasOnly()
    {
        return $this->input->getOption('only');
    }

    /**
     * Checks for the usage of deprecated VERSION as argument when not `all`
     *
     * @return bool True if it is using VERSION argument otherwise false
     */
    protected function isUsingDeprecatedVersion()
    {
        $version = $this->input->getArgument('version');

        return $version && $version !== 'all' && $version !== '*';
    }

    /**
     * Checks for an invalid use of `--exclude` or `--only`
     *
     * @return bool Returns true when it is an invalid use of `--exclude` or `--only` otherwise false
     */
    protected function invalidOnlyOrExclude()
    {
        return ($this->hasExclude() && $this->hasOnly()) ||
            ($this->hasExclude() || $this->hasOnly()) &&
            $this->input->getOption('target') === null;
    }

    /**
     * Outputs the deprecated message for the `all` or `*` usage
     *
     * @return void Just outputs the message
     */
    protected function outputDeprecatedAllMessage()
    {
        $msg = "DEPRECATED: `all` or `*` as version is deprecated. Use `bin/cake migrations mark_migrated` instead";
        $output = $this->output();
        $output->writeln(sprintf("<comment>%s</comment>", $msg));
    }

    /**
     * Outputs the deprecated message for the usage of VERSION as argument
     *
     * @return void Just outputs the message
     */
    protected function outputDeprecatedVersionMessage()
    {
        $msg = 'DEPRECATED: VERSION as argument is deprecated. Use: ' .
            '`bin/cake migrations mark_migrated --target=VERSION --only`';
        $output = $this->output();
        $output->writeln(sprintf("<comment>%s</comment>", $msg));
    }
}
