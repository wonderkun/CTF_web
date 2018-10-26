<% foreach ($columns as $columnName => $columnAttributes):
$type = $columnAttributes['type'];
unset($columnAttributes['type']);

$columnAttributes = $this->Migration->getColumnOption($columnAttributes);
$columnAttributes = $this->Migration->stringifyList($columnAttributes, ['indent' => 4]);
if (!empty($columnAttributes)): %>
            ->addColumn('<%= $columnName %>', '<%= $type %>', [<%= $columnAttributes %>])
<% else: %>
            ->addColumn('<%= $columnName %>', '<%= $type %>')
<% endif; %>
<% endforeach; %>