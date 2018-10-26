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

$tables = $data['fullTables'];
unset($data['fullTables']);
$constraints = [];

$hasUnsignedPk = $this->Migration->hasUnsignedPrimaryKey($tables['add']);

$autoId = true;
if ($hasUnsignedPk) {
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
        <%- foreach ($data as $tableName => $tableDiff):
            $hasRemoveFK = !empty($tableDiff['constraints']['remove']) || !empty($tableDiff['indexes']['remove']);
        %>
        <%- if ($hasRemoveFK): %>
        $this->table('<%= $tableName %>')
        <%- endif; %>
            <%- if (!empty($tableDiff['constraints']['remove'])): %>
            <%- foreach ($tableDiff['constraints']['remove'] as $constraintName => $constraintDefinition): %>
            ->dropForeignKey([], '<%= $constraintName %>')
            <%- endforeach; %>
            <%- endif; %>
            <%- if (!empty($tableDiff['indexes']['remove'])): %>
            <%- foreach ($tableDiff['indexes']['remove'] as $indexName => $indexDefinition): %>
            ->removeIndexByName('<%= $indexName %>')
            <%- endforeach; %>
            <%- endif; %>
        <%- if ($hasRemoveFK): %>
            ->update();
        <%- endif; %>
        <%- if (!empty($tableDiff['columns']['remove']) || !empty($tableDiff['columns']['changed'])): %>

        <%= $this->Migration->tableStatement($tableName, true) %>
        <%- if (!empty($tableDiff['columns']['remove'])): %>
        <%- foreach ($tableDiff['columns']['remove'] as $columnName => $columnDefinition): %>
            ->removeColumn('<%= $columnName %>')
        <%- endforeach; %>
        <%- endif; %>
        <%- if (!empty($tableDiff['columns']['changed'])): %>
        <%- foreach ($tableDiff['columns']['changed'] as $columnName => $columnAttributes):
            $type = $columnAttributes['type'];
            unset($columnAttributes['type']);
            $columnAttributes = $this->Migration->getColumnOption($columnAttributes);
            $columnAttributes = $this->Migration->stringifyList($columnAttributes, ['indent' => 4]);
            if (!empty($columnAttributes)): %>
            ->changeColumn('<%= $columnName %>', '<%= $type %>', [<%= $columnAttributes %>])
            <%- else: %>
            ->changeColumn('<%= $columnName %>', '<%= $type %>')
            <%- endif; %>
        <%- endforeach; %>
        <%- endif; %>
        <%- if (isset($this->Migration->tableStatements[$tableName])): %>
            ->update();
        <%- endif; %>
        <%- endif; %>
        <%- endforeach; %>
        <%- if (!empty($tables['add'])): %>
            <%- echo $this->element('Migrations.create-tables', ['tables' => $tables['add'], 'autoId' => $autoId, 'useSchema' => true]) %>
        <%- endif; %>
        <%- foreach ($data as $tableName => $tableDiff): %>
        <%- if (!empty($tableDiff['columns']['add']) || !empty($tableDiff['indexes']['add'])): %>

        <%= $this->Migration->tableStatement($tableName, true) %>
            <%- if (!empty($tableDiff['columns']['add'])): %>
            <%- echo $this->element('Migrations.add-columns', ['columns' => $tableDiff['columns']['add']]) %>
            <%- endif; %>
            <%- if (!empty($tableDiff['indexes']['add'])): %>
            <%- echo $this->element('Migrations.add-indexes', ['indexes' => $tableDiff['indexes']['add']]) %>
            <%- endif;
            if (isset($this->Migration->tableStatements[$tableName])): %>
            ->update();
            <%- endif; %>
            <%- endif; %>
        <%- endforeach; %>
        <%- foreach ($data as $tableName => $tableDiff): %>
        <%- if (!empty($tableDiff['constraints']['add'])): %>
            <%- echo $this->element(
                'Migrations.add-foreign-keys',
                ['constraints' => $tableDiff['constraints']['add'], 'table' => $tableName]
            ); %>
            <%- endif; %>
        <%- endforeach; %>
        <%- if (!empty($tables['remove'])): %>
        <%- foreach ($tables['remove'] as $tableName => $table): %>

        $this->dropTable('<%= $tableName %>');
            <%- endforeach; %>
        <%- endif; %>
    }

    public function down()
    {
        <%- $constraints = [];
        $emptyLine = false;
        if (!empty($this->Migration->returnedData['dropForeignKeys'])):
            foreach ($this->Migration->returnedData['dropForeignKeys'] as $table => $columnsList):
                $maxKey = count($columnsList) - 1;
                if ($emptyLine === true): %>

                <%- else:
                    $emptyLine = true;
                endif; %>
        $this->table('<%= $table %>')
                <%- foreach ($columnsList as $key => $columns): %>
            ->dropForeignKey(
                <%= $columns %>
            )<%= ($key === $maxKey) ? ';' : '' %>
                <%- endforeach; %>
            <%- endforeach; %>
        <%- endif; %>
        <%- if (!empty($tables['remove'])): %>
            <%- echo $this->element('Migrations.create-tables', ['tables' => $tables['remove'], 'autoId' => $autoId, 'useSchema' => true]) %>
        <%- endif; %>
        <%- foreach ($data as $tableName => $tableDiff):
                unset($this->Migration->tableStatements[$tableName]);
                if (!empty($tableDiff['indexes']['add'])): %>

        $this->table('<%= $tableName %>')
                <%- foreach ($tableDiff['indexes']['add'] as $indexName => $index): %>
            ->removeIndexByName('<%= $indexName %>')
                <%- endforeach %>
            ->update();
            <%- endif; %>
        <%- if (!empty($tableDiff['columns']['remove']) ||
            !empty($tableDiff['columns']['changed']) ||
            !empty($tableDiff['columns']['add']) ||
            !empty($tableDiff['indexes']['remove'])
        ): %>

        <%= $this->Migration->tableStatement($tableName, true) %>
        <%- endif; %>
        <%- if (!empty($tableDiff['columns']['remove'])): %>
        <%- echo $this->element('Migrations.add-columns', ['columns' => $tableDiff['columns']['remove']]) %>
        <%- endif; %>
        <%- if (!empty($tableDiff['columns']['changed'])):
            $oldTableDef = $dumpSchema[$tableName];
            foreach ($tableDiff['columns']['changed'] as $columnName => $columnAttributes):
            $columnAttributes = $oldTableDef->column($columnName);
            $type = $columnAttributes['type'];
            unset($columnAttributes['type']);
            $columnAttributes = $this->Migration->getColumnOption($columnAttributes);
            $columnAttributes = $this->Migration->stringifyList($columnAttributes, ['indent' => 4]);
            if (!empty($columnAttributes)): %>
            ->changeColumn('<%= $columnName %>', '<%= $type %>', [<%= $columnAttributes %>])
            <%- else: %>
            ->changeColumn('<%= $columnName %>', '<%= $type %>')
            <%- endif; %>
            <%- endforeach; %>
        <%- endif; %>
        <%- if (!empty($tableDiff['columns']['add'])): %>
            <%- foreach ($tableDiff['columns']['add'] as $columnName => $columnAttributes): %>
            ->removeColumn('<%= $columnName %>')
            <%- endforeach; %>
        <%- endif; %>
            <%- if (!empty($tableDiff['indexes']['remove'])): %>
            <%- echo $this->element('Migrations.add-indexes', ['indexes' => $tableDiff['indexes']['remove']]) %>
            <%- endif;
            if (isset($this->Migration->tableStatements[$tableName])): %>
            ->update();
            <%- endif; %>
        <%- endforeach; %>
        <%- foreach ($data as $tableName => $tableDiff): %>
            <%- if (!empty($tableDiff['constraints']['remove'])): %>
                <%- echo $this->element(
                    'Migrations.add-foreign-keys',
                    ['constraints' => $tableDiff['constraints']['remove'], 'table' => $tableName]
                ); %>
            <%- endif; %>
        <%- endforeach; %>
        <%- if (!empty($tables['add'])): %>
            <%- foreach ($tables['add'] as $tableName => $table): %>

        $this->dropTable('<%= $tableName %>');
            <%- endforeach; %>
        <%- endif; %>
    }
}

