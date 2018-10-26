<%
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
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Database\Schema\Table;

$constraints = $foreignKeys = $dropForeignKeys = [];
$hasUnsignedPk = $this->Migration->hasUnsignedPrimaryKey($tables);

if ($autoId && $hasUnsignedPk) {
    $autoId = false;
}
%>
<?php
use Migrations\AbstractMigration;

class <%= $name %> extends AbstractMigration
{
    <%- if (!$autoId): %>

    public $autoId = false;

    <%- endif; %>
    public function up()
    {
        <%- echo $this->element('Migrations.create-tables', ['tables' => $tables, 'autoId' => $autoId, 'useSchema' => false]) %>
    }

    public function down()
    {
        <%- if (!empty($this->Migration->returnedData['dropForeignKeys'])):
            foreach ($this->Migration->returnedData['dropForeignKeys'] as $table => $columnsList):
                $maxKey = count($columnsList) - 1;
        %>
        $this->table('<%= $table %>')
            <%- foreach ($columnsList as $key => $columns): %>
            ->dropForeignKey(
                <%= $columns %>
            )<%= ($key === $maxKey) ? ';' : '' %>
            <%- endforeach; %>

        <%- endforeach;
            endif;
        %>
        <%- foreach ($tables as $table): %>
        $this->dropTable('<%= $table%>');
        <%- endforeach; %>
    }
}
