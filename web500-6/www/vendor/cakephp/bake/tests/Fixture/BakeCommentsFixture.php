<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BakeCommentFixture fixture for testing bake
 *
 */
class BakeCommentsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'otherid' => ['type' => 'integer'],
        'bake_article_id' => ['type' => 'integer', 'null' => false],
        'bake_user_id' => ['type' => 'integer', 'null' => false],
        'comment' => 'text',
        'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
        'created' => 'datetime',
        'updated' => 'datetime',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['otherid']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [];
}
