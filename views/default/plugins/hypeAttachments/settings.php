<?php

$entity = elgg_extract('entity', $vars);

$dbprefix = elgg_get_config('dbprefix');

$qb = \Elgg\Database\Select::fromTable('entities');
$qb->select(['subtype'])
	->groupBy('subtype')
	->where($qb->compare('type', '=', 'object', ELGG_VALUE_STRING));

$rows = elgg()->db->getData($qb);

$options = [];
$values = [];

ob_start();
foreach ($rows as $row) {
	$subtype = $row->subtype;
	echo elgg_view_field([
		'#type' => 'checkbox',
		'name' => "params[object:$subtype]",
		'value' => '1',
		'default' => '0',
		'checked' => (bool) $entity->{"object:$subtype"},
		'label' => elgg_echo("collection:object:$subtype"),
	]);
}
$inputs = ob_get_clean();

echo elgg_view('elements/forms/field', [
	'input' => $inputs,
	'label' => elgg_echo('attachments:settings:allow_attachments'),
]);
