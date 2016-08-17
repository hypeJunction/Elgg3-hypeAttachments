<?php

$entity = elgg_extract('entity', $vars);

$dbprefix = elgg_get_config('dbprefix');
$sql = "
	SELECT *
	FROM {$dbprefix}entity_subtypes
	WHERE type = :type
";
$params = [':type' => 'object'];

$rows = get_data($sql, null, $params);

$options = [];
$values = [];

ob_start();
foreach ($rows as $row) {
	$type = $row->type;
	$subtype = $row->subtype;
	echo elgg_view_input('checkbox', [
		'name' => "params[$type:$subtype]",
		'value' => '1',
		'default' => '0',
		'checked' => (bool) $entity->{"$type:$subtype"},
		'label' => elgg_echo("item:$type:$subtype"),
	]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
	'input' => $inputs,
	'label' => elgg_echo('attachments:settings:allow_attachments'),
]);
