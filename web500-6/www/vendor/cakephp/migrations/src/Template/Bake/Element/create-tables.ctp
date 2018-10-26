<% foreach ($tables as $table => $schema):
    $tableArgForMethods = $useSchema === true ? $schema : $table;
    $tableArgForArray = $useSchema === true ? $table : $schema;

$foreignKeys = [];
$primaryKeysColumns = $this->Migration->primaryKeysColumnsList($tableArgForMethods);
$primaryKeys = $this->Migration->primaryKeys($tableArgForMethods);
$specialPk = (count($primaryKeys) > 1 || $primaryKeys[0]['name'] !== 'id' || $primaryKeys[0]['info']['columnType'] !== 'integer') && $autoId;
%>
<% if ($specialPk): %>

        $this->table('<%= $tableArgForArray %>', ['id' => false, 'primary_key' => ['<%= implode("', '", \Cake\Utility\Hash::extract($primaryKeys, '{n}.name')) %>']])
<% else: %>

        $this->table('<%= $tableArgForArray %>')
<% endif; %>
<% if ($specialPk || !$autoId):
    foreach ($primaryKeys as $primaryKey) :
%>
            ->addColumn('<%= $primaryKey['name'] %>', '<%= $primaryKey['info']['columnType'] %>', [<%
            $columnOptions = $this->Migration->getColumnOption($primaryKey['info']['options']);
            echo $this->Migration->stringifyList($columnOptions, ['indent' => 4]);
            %>])
<% endforeach; %>
<% if (!$autoId): %>
            ->addPrimaryKey(['<%= implode("', '", \Cake\Utility\Hash::extract($primaryKeys, '{n}.name')) %>'])
<% endif; %>
<% endif;
foreach ($this->Migration->columns($tableArgForMethods) as $column => $config):
%>
            ->addColumn('<%= $column %>', '<%= $config['columnType'] %>', [<%
            $columnOptions = $this->Migration->getColumnOption($config['options']);
            if ($config['columnType'] === 'boolean' && isset($columnOptions['default']) && $this->Migration->value($columnOptions['default']) !== 'null'):
                $columnOptions['default'] = (bool)$columnOptions['default'];
            endif;
            echo $this->Migration->stringifyList($columnOptions, ['indent' => 4]);
            %>])
<% endforeach;
$tableConstraints = $this->Migration->constraints($tableArgForMethods);
if (!empty($tableConstraints)):
    sort($tableConstraints);
    $constraints[$tableArgForArray] = $tableConstraints;

    foreach ($constraints[$tableArgForArray] as $name => $constraint):
        if ($constraint['type'] === 'foreign'):
            $foreignKeys[] = $constraint['columns'];
        endif;
        if ($constraint['columns'] !== $primaryKeysColumns): %>
            ->addIndex(
                [<% echo $this->Migration->stringifyList($constraint['columns'], ['indent' => 5]); %>]<% echo ($constraint['type'] === 'unique') ? ',' : ''; %>

<% if ($constraint['type'] === 'unique'): %>
                ['unique' => true]
<% endif; %>
            )
<% endif;
    endforeach;
endif;
foreach($this->Migration->indexes($tableArgForMethods) as $index):
    sort($foreignKeys);
    $indexColumns = $index['columns'];
    sort($indexColumns);
    if (!in_array($indexColumns, $foreignKeys)):
        %>
            ->addIndex(
                [<%
                    echo $this->Migration->stringifyList($index['columns'], ['indent' => 5]);
                %>]<% echo ($index['type'] === 'fulltext') ? ',' : ''; %>

                <%- if ($index['type'] === 'fulltext'): %>
                ['type' => 'fulltext']
                <%- endif; %>
            )
<% endif;
endforeach; %>
            ->create();
<% endforeach; %>
<% if (!empty($constraints)): %>
<% echo $this->element('Migrations.add-foreign-keys-from-create', ['constraints' => $constraints]); %>
<% endif; %>