<% foreach ($indexes as $indexName => $index): %>
            ->addIndex(
                [<% echo $this->Migration->stringifyList($index['columns'], ['indent' => 5]); %>],
                [<%
                    $params = ['name' => $indexName];
                    if ($index['type'] === 'unique'):
                        $params['unique'] = true;
                    endif;
                    echo $this->Migration->stringifyList($params, ['indent' => 5]);
                %>]
            )
<% endforeach; %>