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

$associations += ['BelongsTo' => [], 'HasOne' => [], 'HasMany' => [], 'BelongsToMany' => []];
$immediateAssociations = $associations['BelongsTo'];
$associationFields = collection($fields)
    ->map(function($field) use ($immediateAssociations) {
        foreach ($immediateAssociations as $alias => $details) {
            if ($field === $details['foreignKey']) {
                return [$field => $details];
            }
        }
    })
    ->filter()
    ->reduce(function($fields, $value) {
        return $fields + $value;
    }, []);

$groupedFields = collection($fields)
    ->filter(function($field) use ($schema) {
        return $schema->columnType($field) !== 'binary';
    })
    ->groupBy(function($field) use ($schema, $associationFields) {
        $type = $schema->columnType($field);
        if (isset($associationFields[$field])) {
            return 'string';
        }
        if (in_array($type, ['integer', 'float', 'decimal', 'biginteger'])) {
            return 'number';
        }
        if (in_array($type, ['date', 'time', 'datetime', 'timestamp'])) {
            return 'date';
        }
        return in_array($type, ['text', 'boolean']) ? $type : 'string';
    })
    ->toArray();

$groupedFields += ['number' => [], 'string' => [], 'boolean' => [], 'date' => [], 'text' => []];
$pk = "$singularVar.{$primaryKey[0]}";
%>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading">{{ __('Actions') }}</li>
        <li>{{ Html.link(__('Edit <%= $singularHumanName %>'), {'action' : 'edit', 0 : <%= $pk %>})|raw }}</li>
        <li>{{ Form.postLink(__('Delete <%= $singularHumanName %>'), {'action' : 'delete', 0 : <%= $pk %>}, {'confirm' : __('Are you sure you want to delete # {0}?', <%= $pk %>)})|raw }}</li>
        <li>{{ Html.link(__('List <%= $pluralHumanName %>'), {'action' : 'index'})|raw }}</li>
        <li>{{ Html.link(__('New <%= $singularHumanName %>'), {'action' : 'add'})|raw }}</li>
<%
    $done = [];
    foreach ($associations as $type => $data) {
        foreach ($data as $alias => $details) {
            if ($details['controller'] !== $this->name && !in_array($details['controller'], $done)) {
%>
        <li>{{ Html.link(__('List <%= $this->_pluralHumanName($alias) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'index'})|raw }}</li>
        <li>{{ Html.link(__('New <%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'add'})|raw }}</li>
<%
                $done[] = $details['controller'];
            }
        }
    }
%>
    </ul>
</nav>
<div class="<%= $pluralVar %> view large-9 medium-8 columns content">
    <h3>{{ <%= $singularVar %>.<%= $displayField %>|h }}</h3>
    <table class="vertical-table">
<% if ($groupedFields['string']) : %>
<% foreach ($groupedFields['string'] as $field) : %>
<% if (isset($associationFields[$field])) :
            $details = $associationFields[$field];
%>
        <tr>
            <th>{{ __('<%= Inflector::humanize($details['property']) %>') }}</th>
            <td>{{ <%= $singularVar %>.has('<%= $details['property'] %>') ? Html.link(<%= $singularVar %>.<%= $details['property'] %>.<%= $details['displayField'] %>, {'controller' : '<%= $details['controller'] %>', 'action' : 'view', 0 : <%= $singularVar %>.<%= $details['property'] %>.<%= $details['primaryKey'][0] %>})|raw : '' }}</td>
        </tr>
<% else : %>
        <tr>
            <th>{{ __('<%= Inflector::humanize($field) %>') }}</th>
            <td>{{ <%= $singularVar %>.<%= $field %>|h }}</td>
        </tr>
<% endif; %>
<% endforeach; %>
<% endif; %>
<% if ($associations['HasOne']) : %>
    <%- foreach ($associations['HasOne'] as $alias => $details) : %>
        <tr>
            <th>{{ __('<%= Inflector::humanize(Inflector::singularize(Inflector::underscore($alias))) %>') }}</th>
            <td>{{ <%= $singularVar %>.has('<%= $details['property'] %>') ? Html.link(<%= $singularVar %>.<%= $details['property'] %>.<%= $details['displayField'] %>, {'controller' : '<%= $details['controller'] %>', 'action' : 'view', 0 : <%= $singularVar %>.<%= $details['property'] %>.<%= $details['primaryKey'][0] %>})|raw : '' }}</td>
        </tr>
    <%- endforeach; %>
<% endif; %>
<% if ($groupedFields['number']) : %>
<% foreach ($groupedFields['number'] as $field) : %>
        <tr>
            <th>{{ __('<%= Inflector::humanize($field) %>') }}</th>
            <td>{{ Number.format(<%= $singularVar %>.<%= $field %>) }}</td>
        </tr>
<% endforeach; %>
<% endif; %>
<% if ($groupedFields['date']) : %>
<% foreach ($groupedFields['date'] as $field) : %>
        <tr>
            <th>{{ __('<%= Inflector::humanize($field) %>') }}</th>
            <td>{{ <%= $singularVar %>.<%= $field %>|h }}</td>
        </tr>
<% endforeach; %>
<% endif; %>
<% if ($groupedFields['boolean']) : %>
<% foreach ($groupedFields['boolean'] as $field) : %>
        <tr>
            <th>{{ __('<%= Inflector::humanize($field) %>') }}</th>
            <td>{{ <%= $singularVar %>.<%= $field %> ? __('Yes') : __('No'); }}</td>
        </tr>
<% endforeach; %>
<% endif; %>
    </table>
<% if ($groupedFields['text']) : %>
<% foreach ($groupedFields['text'] as $field) : %>
    <div class="row">
        <h4>{{ __('<%= Inflector::humanize($field) %>') }}</h4>
        {{ Text.autoParagraph(<%= $singularVar %>.<%= $field %>|h)|raw }}
    </div>
<% endforeach; %>
<% endif; %>
<%
$relations = $associations['HasMany'] + $associations['BelongsToMany'];
foreach ($relations as $alias => $details):
    $otherSingularVar = Inflector::variable($alias);
    $otherPluralHumanName = Inflector::humanize(Inflector::underscore($details['controller']));
    %>
    <div class="related">
        <h4>{{ __('Related <%= $otherPluralHumanName %>') }}</h4>
        {% if (<%= $singularVar %>.<%= $details['property'] %> is not empty): %}
        <table cellpadding="0" cellspacing="0">
            <tr>
<% foreach ($details['fields'] as $field): %>
                <th>{{ __('<%= Inflector::humanize($field) %>') }}</th>
<% endforeach; %>
                <th class="actions">{{ __('Actions') }}</th>
            </tr>
            {% for (<%= $otherSingularVar %> in <%= $singularVar %>.<%= $details['property'] %>): %}
            <tr>
            <%- foreach ($details['fields'] as $field): %>
                <td>{{ <%= $otherSingularVar %>.<%= $field %>|h }}</td>
            <%- endforeach; %>
            <%- $otherPk = "{$otherSingularVar}.{$details['primaryKey'][0]}"; %>
                <td class="actions">
                    {{ Html.link(__('View'), {'controller' : '<%= $details['controller'] %>', 'action' : 'view', 0 : <%= $otherPk %>})|raw }}
                    {{ Html.link(__('Edit'), {'controller' : '<%= $details['controller'] %>', 'action' : 'edit', 0 : <%= $otherPk %>})|raw }}
                    {{ Form.postLink(__('Delete'), {'controller' : '<%= $details['controller'] %>', 'action' : 'delete', 0 : <%= $otherPk %>], {'confirm' : __('Are you sure you want to delete # {0}?', <%= $otherPk %>)})|raw }}
                </td>
            </tr>
            {% endfor %}
        </table>
        {% endif %}
    </div>
<% endforeach; %>
</div>