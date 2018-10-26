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
namespace Migrations\Util;

use Cake\Datasource\ConnectionManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Trait gathering schema collection used for caching
 */
trait SchemaTrait
{

    /**
     * Helper method to get the schema collection.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Input object.
     * @param \Symfony\Component\Console\Input\OutputInterface $output Output object.
     * @return null|\Cake\Database\Schema\Collection
     */
    protected function _getSchema(InputInterface $input, OutputInterface $output)
    {
        $connectionName = $input->getOption('connection');
        $connection = ConnectionManager::get($connectionName);

        if (!method_exists($connection, 'schemaCollection')) {
            $msg = sprintf(
                'The "%s" connection is not compatible with orm caching, ' .
                'as it does not implement a "schemaCollection()" method.',
                $connectionName
            );
            $output->writeln('<error>' . $msg . '</error>');

            return null;
        }

        $config = $connection->config();

        if (empty($config['cacheMetadata'])) {
            $output->writeln('Metadata cache was disabled in config. Enable to cache or clear.');

            return null;
        }

        $connection->cacheMetadata(true);

        return $connection->schemaCollection();
    }
}
