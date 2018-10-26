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
        return !in_array($schema->columnType($field), ['binary', 'text']);
    });

if (isset($modelObject) && $modelObject->behaviors()->has('Tree')) {
    $fields = $fields->reject(function ($field) {
        return $field === 'lft' || $field === 'rght';
    });
}

if (!empty($indexColumns)) {
    $fields = $fields->take($indexColumns);
}

%>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading">{{ __('Actions') }}</li>
        <li>{{ Html.link(__('New <%= $singularHumanName %>'), {'action' : 'add'})|raw }}</li>
<%
    $done = [];
    foreach ($associations as $type => $data):
        foreach ($data as $alias => $details):
            if (!empty($details['navLink']) && $details['controller'] !== $this->name && !in_array($details['controller'], $done)):
%>
        <li>{{ Html.link(__('List <%= $this->_pluralHumanName($alias) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'index'})|raw }}</li>
        <li>{{ Html.link(__('New <%= $this->_singularHumanName($alias) %>'), {'controller' : '<%= $details['controller'] %>', 'action' : 'add'})|raw }}</li>
<%
                $done[] = $details['controller'];
            endif;
        endforeach;
    endforeach;
%>
    </ul>
</nav>
<div class="<%= $pluralVar %> index large-9 medium-8 columns content">
    <h3>{{ __('<%= $pluralHumanName %>') }}</h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
<% foreach ($fields as $field): %>
                <th>{{ Paginator.sort('<%= $field %>')|raw }}</th>
<% endforeach; %>
                <th class="actions">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            {% for <%= $singularVar %> in <%= $pluralVar %> %}
            <tr>
<%        foreach ($fields as $field) {
            $isKey = false;
            if (!empty($associations['BelongsTo'])) {
                foreach ($associations['BelongsTo'] as $alias => $details) {
                    if ($field === $details['foreignKey']) {
                        $isKey = true;
%>
                <td>{{ <%= $singularVar %>.has('<%= $details['property'] %>') ? Html.link(<%= $singularVar %>.<%= $details['property'] %>.<%= $details['displayField'] %>, {'controller' : '<%= $details['controller'] %>', 'action' : 'view', 0 : <%= $singularVar %>.<%= $details['property'] %>.<%= $details['primaryKey'][0] %>})|raw : '' }}</td>
<%
                        break;
                    }
                }
            }
            if ($isKey !== true) {
                if (!in_array($schema->columnType($field), ['integer', 'biginteger', 'decimal', 'float'])) {
%>
                <td>{{ <%= $singularVar %>.<%= $field %>|h }}</td>
<%
                } else {
%>
                <td>{{ Number.format(<%= $singularVar %>.<%= $field %>) }}</td>
<%
                }
            }
        }

        $pk = $singularVar . '.' . $primaryKey[0];
%>
                <td class="actions">
                    {{ Html.link(__('View'), {'action' : 'view', 0 : <%= $pk %>})|raw }}
                    {{ Html.link(__('Edit'), {'action' : 'edit', 0 : <%= $pk %>})|raw }}
                    {{ Form.postLink(__('Delete'), {'action' : 'delete', 0 : <%= $pk %>}, {'confirm' : __('Are you sure you want to delete # {0}?', <%= $pk %>)})|raw }}
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            {{ Paginator.prev('< ' ~ __('previous'))|raw }}
            {{ Paginator.numbers()|raw }}
            {{ Paginator.next(__('next') ~ ' >')|raw }}
        </ul>
        <p>{{ Paginator.counter()|raw }}</p>
    </div>
</div>