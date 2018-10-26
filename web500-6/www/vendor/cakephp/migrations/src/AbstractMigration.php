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

use Phinx\Migration\AbstractMigration as BaseAbstractMigration;

class AbstractMigration extends BaseAbstractMigration
{

    /**
     * Whether the tables created in this migration
     * should auto-create an `id` field or not
     *
     * This option is global for all tables created in the migration file.
     * If you set it to false, you have to manually add the primary keys for your
     * tables using the Migrations\Table::addPrimaryKey() method
     *
     * @var bool
     */
    public $autoId = true;

    /**
     * {@inheritdoc}
     *
     * @return \Migrations\Table
     */
    public function table($tableName, $options = [])
    {
        if ($this->autoId === false) {
            $options['id'] = false;
        }

        return new Table($tableName, $options, $this->getAdapter());
    }
}
