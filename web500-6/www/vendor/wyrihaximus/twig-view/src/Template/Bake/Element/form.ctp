<%
/**
 * This file is part of TwigView.
 *
 ** (c) 2015 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Cake\Utility\Inflector;

$fields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    });

if (isset($modelObject) && $modelObject->behaviors()->has('Tree')) {
    $fields = $fields->reject(function ($field) {
        return $field === 'lft' || $field === 'rght';
    });
}
%>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading">{{ __('Actions') }}</li>
<% if (strpos($action, 'add') === false): %>
        <li>{{ Form.postLink(
                __('Delete'),
                {'action' : 'delete', 0 : <%= $singularVar %>.<%= $primaryKey[0] %>},
                {'confirm' : __('Are you sure you want to delete # {0}?', <%= $singularVar %>.<%= $primaryKey[0] %>)}
            )|raw
            }}
        </li>
<% endif; %>
        <li>{{ Html.link(__('List <%= $pluralHumanName %>'), {'action' : 'index'})|raw }}</li>
<%
        $done = [];
        foreach ($associations as $type => $data) {
            foreach ($data as $alias => $details) {
                if ($details['controller'] !== $this->name && !in_array($details['controller'], $done)) {
%>
        <li>{{ Html.link(__('List <%= $this->_pluralHumanName($alias) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'index'})|raw }}</li>
        <li>{{ Html.link(__('New <%= $this->_singularHumanName($alias) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'add'})|raw }}</li>
<%
                    $done[] = $details['controller'];
                }
            }
        }
%>
    </ul>
</nav>
<div class="<%= $pluralVar %> form large-9 medium-8 columns content">
    {{ Form.create(<%= $singularVar %>)|raw }}
    <fieldset>
        <legend>{{ __('<%= Inflector::humanize($action) %> <%= $singularHumanName %>') }}</legend>
<%
        foreach ($fields as $field) {
            if (in_array($field, $primaryKey)) {
                continue;
            }
            if (isset($keyFields[$field])) {
                $fieldData = $schema->column($field);
                if (!empty($fieldData['null'])) {
%>
        {{ Form.input('<%= $field %>', {'options' : <%= $keyFields[$field] %>, 'empty' : true})|raw }}
<%
                } else {
%>
        {{ Form.input('<%= $field %>', {'options' : <%= $keyFields[$field] %>})|raw }}
<%
                }
                continue;
            }
            if (!in_array($field, ['created', 'modified', 'updated'])) {
                $fieldData = $schema->column($field);
                if (in_array($fieldData['type'], ['date', 'datetime', 'time']) && (!empty($fieldData['null']))) {
%>
        {{ Form.input('<%= $field %>', {'empty' : true})|raw }}
<%
                } else {
%>
        {{ Form.input('<%= $field %>')|raw }}
<%
                }
            }
        }
        if (!empty($associations['BelongsToMany'])) {
            foreach ($associations['BelongsToMany'] as $assocName => $assocData) {
%>
        {{ Form.input('<%= $assocData['property'] %>._ids', {'options' : <%= $assocData['variable'] %>})|raw }}
<%
            }
        }
%>
    </fieldset>
    {{ Form.button(__('Submit'))|raw }}
    {{ Form.end()|raw }}
</div>