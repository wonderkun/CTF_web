<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.2.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BakeAuthors Fixture
 */
class BakeTemplateAuthorsFixture extends TestFixture
{

    /**
     * Avoid overriding core.authors
     * @var string
     */
    public $table = 'bake_authors';

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'role_id' => ['type' => 'integer', 'null' => false],
        'name' => ['type' => 'string', 'default' => null],
        'description' => ['type' => 'text', 'default' => null],
        'member' => ['type' => 'boolean'],
        'member_number' => ['type' => 'integer', 'null' => true],
        'account_balance' => ['type' => 'decimal', 'null' => true, 'precision' => 2, 'length' => 12],
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['name' => 'mariano', 'role_id' => 1],
        ['name' => 'nate', 'role_id' => 2],
        ['name' => 'larry', 'role_id' => 2],
        ['name' => 'garrett', 'role_id' => 1],
    ];
}
