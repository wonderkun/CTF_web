<% foreach ($constraints as $table => $tableConstraints):
    echo $this->element('Migrations.add-foreign-keys', ['constraints' => $tableConstraints, 'table' => $table]);
endforeach; %>