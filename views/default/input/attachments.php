<?php
/**
 * Attachments input
 * @uses $vars['name']   Input name
 * @uses $vars['expand'] Expand the attachments input by default
 * @uses $vars['max']    Max uploaded files
 */
$max = elgg_extract('max', $vars, 25);

if (elgg_view_exists('input/dropzone')) {
	$uploads_form = elgg_view('input/dropzone', [
		'name' => elgg_extract('name', $vars, 'uploads'),
		'max' => $max,
		'multiple' => $max > 1,
		'id' => elgg_extract('id', $vars),
	]);
} else {
	$uploads_form = elgg_view('input/file', [
		'name' => elgg_extract('name', $vars, 'uploads') . '[]',
		'multiple' => $max > 1,
		'id' => elgg_extract('id', $vars),
	]);
}

$class = ['attachments-fieldset'];
if (!elgg_extract('expand', $vars, true)) {
	$class[] = 'hidden';
	echo elgg_view('output/url', [
		'text' => elgg_echo('attachments:upload'),
		'href' => '#',
		'class' => 'attachments-toggler',
	]);
}

echo elgg_format_element('div', [
	'class' => $class,
		], $uploads_form);

// @todo: update in Elgg 2.2 to use elgg_require_js()
?>
<script>require(['input/attachments']);</script>
