<?php
namespace Migrations\Command;

use Cake\Cache\Cache;
use Migrations\Util\SchemaTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClear extends Command
{
    use SchemaTrait;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('orm-cache-clear')
            ->setDescription('Clear all metadata caches for the connection. If a table name is provided, only that table will be removed.')
            ->addOption(
                'connection',
                null,
                InputOption::VALUE_OPTIONAL,
                'The connection to build/clear metadata cache data for.',
                'default'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'A specific table you want to clear/refresh cached data for.'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $this->_getSchema($input, $output);
        $name = $input->getArgument('name');
        if (!$schema) {
            return false;
        }
        $tables = [$name];
        if (empty($name)) {
            $tables = $schema->listTables();
        }
        $configName = $schema->cacheMetadata();

        foreach ($tables as $table) {
            $output->writeln(sprintf(
                'Clearing metadata cache from "%s" for %s',
                $configName,
                $table
            ));
            $key = $schema->cacheKey($table);
            Cache::delete($key, $configName);
        }
        $output->writeln('<info>Cache clear complete<info>');

        return true;
    }
}
