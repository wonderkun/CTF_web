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

$wantedOptions = array_flip(['length', 'limit', 'default', 'unsigned', 'null', 'comment', 'autoIncrement']);
$tableMethod = $this->Migration->tableMethod($action);
$columnMethod = $this->Migration->columnMethod($action);
$indexMethod = $this->Migration->indexMethod($action);
%>
<?php
use Migrations\AbstractMigration;

class <%= $name %> extends AbstractMigration
{
    <%- if ($tableMethod === 'create' && !empty($columns['primaryKey'])): %>

    public $autoId = false;

    <%- endif; %>
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
<% foreach ($tables as $table): %>
        $table = $this->table('<%= $table%>');
<% if ($tableMethod !== 'drop') : %>
<% if ($columnMethod === 'removeColumn'): %>
<% foreach ($columns['fields'] as $column => $config): %>
        <%= "\$table->$columnMethod('" . $column . "');"; %>
<% endforeach; %>
<% foreach ($columns['indexes'] as $column => $config): %>
        <%= "\$table->$indexMethod([" . $this->Migration->stringifyList($config['columns']) . ");"; %>
<% endforeach; %>
<% else : %>
<% foreach ($columns['fields'] as $column => $config): %>
        $table-><%= $columnMethod %>('<%= $column %>', '<%= $config['columnType'] %>', [<%
                $columnOptions = $config['options'];
                $columnOptions = array_intersect_key($columnOptions, $wantedOptions);
                if (empty($columnOptions['comment'])) {
                    unset($columnOptions['comment']);
                }
                echo $this->Migration->stringifyList($columnOptions, ['indent' => 3]);
            %>]);
<% endforeach; %>
<% foreach ($columns['indexes'] as $column => $config): %>
        $table-><%= $indexMethod %>([<%=
                $this->Migration->stringifyList($config['columns'], ['indent' => 3])
                %>], [<%
                $options = [];
                echo $this->Migration->stringifyList($config['options'], ['indent' => 3]);
            %>]);
<% endforeach; %>
<% if ($tableMethod === 'create' && !empty($columns['primaryKey'])): %>
        $table->addPrimaryKey([<%=
                $this->Migration->stringifyList($columns['primaryKey'], ['indent' => 3])
                %>]);
<% endif; %>
<% endif; %>
<% endif; %>
        $table-><%= $tableMethod %>();
<% endforeach; %>
    }
}
