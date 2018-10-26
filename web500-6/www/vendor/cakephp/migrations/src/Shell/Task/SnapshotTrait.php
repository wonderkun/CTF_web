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
namespace Migrations\Shell\Task;

use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Database\Schema\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use ReflectionClass;

/**
 * Trait needed for all "snapshot" type of bake operations.
 * Snapshot type operations are : baking a snapshot and baking a diff.
 */
trait SnapshotTrait
{

    /**
     * After a file has been successfully created, we mark the newly
     * created migration as applied
     *
     * @param string $path Where to put the file.
     * @param string $contents Content to put in the file.
     * @return bool Success
     */
    public function createFile($path, $contents)
    {
        $createFile = parent::createFile($path, $contents);

        if ($createFile) {
            $this->markSnapshotApplied($path);

            if (!isset($this->params['no-lock']) || !$this->params['no-lock']) {
                $this->refreshDump();
            }
        }

        return $createFile;
    }

    /**
     * Will mark a snapshot created, the snapshot being identified by its
     * full file path.
     *
     * @param string $path Path to the newly created snapshot
     * @return void
     */
    protected function markSnapshotApplied($path)
    {
        $fileName = pathinfo($path, PATHINFO_FILENAME);
        list($version, ) = explode('_', $fileName, 2);

        $dispatchCommand = 'migrations mark_migrated -t ' . $version . ' -o';
        if (!empty($this->params['connection'])) {
            $dispatchCommand .= ' -c ' . $this->params['connection'];
        }

        if (!empty($this->params['plugin'])) {
            $dispatchCommand .= ' -p ' . $this->params['plugin'];
        }

        $this->_io->out('Marking the migration ' . $fileName . ' as migrated...');
        $this->dispatchShell($dispatchCommand);
    }

    /**
     * After a file has been successfully created, we refresh the dump of the database
     * to be able to generate a new diff afterward.
     *
     * @return void
     */
    protected function refreshDump()
    {
        $dispatchCommand = 'migrations dump';
        if (!empty($this->params['connection'])) {
            $dispatchCommand .= ' -c ' . $this->params['connection'];
        }

        if (!empty($this->params['plugin'])) {
            $dispatchCommand .= ' -p ' . $this->params['plugin'];
        }

        $this->_io->out('Creating a dump of the new database state...');
        $this->dispatchShell($dispatchCommand);
    }
}
