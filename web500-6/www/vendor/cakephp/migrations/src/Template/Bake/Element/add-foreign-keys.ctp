<%
$statement = $this->Migration->tableStatement($table, true);
$hasProcessedConstraint = false;
%>
<% foreach ($constraints as $constraint):
    $constraintColumns = $constraint['columns'];
    sort($constraintColumns);
    if ($constraint['type'] !== 'unique'):
        $hasProcessedConstraint = true;
        $columnsList = '\'' . $constraint['columns'][0] . '\'';
        if (count($constraint['columns']) > 1):
            $columnsList = '[' . $this->Migration->stringifyList($constraint['columns'], ['indent' => 5]) . ']';
        endif;
        $this->Migration->returnedData['dropForeignKeys'][$table][] = $columnsList;

        if (is_array($constraint['references'][1])):
            $columnsReference = '[' . $this->Migration->stringifyList($constraint['references'][1], ['indent' => 5]) . ']';
        else:
            $columnsReference = '\'' . $constraint['references'][1] . '\'';
        endif;

        if (!isset($statement)):
            $statement = $this->Migration->tableStatement($table);
        endif;

        if (!empty($statement)): %>

        <%= $statement %>
<% unset($statement);
    endif; %>
            ->addForeignKey(
                <%= $columnsList %>,
                '<%= $constraint['references'][0] %>',
                <%= $columnsReference %>,
                [
                    'update' => '<%= $this->Migration->formatConstraintAction($constraint['update']) %>',
                    'delete' => '<%= $this->Migration->formatConstraintAction($constraint['delete']) %>'
                ]
            )
<% endif; %>
<% endforeach; %>
<% if (isset($this->Migration->tableStatements[$table]) && $hasProcessedConstraint): %>
            ->update();
<% endif; %>