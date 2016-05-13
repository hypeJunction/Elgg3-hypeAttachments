<?php

/**
 * Attachments input
 * @uses $vars['name']   Input name
 * @uses $vars['expand'] Expand the attachments input by default
 * @uses $vars['max']    Max uploaded files
 */

$max = elgg_extract('max', $vars, 25);
$uploads_form = elgg_view('input/dropzone', array(
	'name' => elgg_extract('name', $vars, 'uploads'),
	'max' => $max,
	'multiple' => $max > 1,
		));

$class = ['attachments-fieldset'];
if (!elgg_extract('expand', $vars)) {
	$class[] = 'hidden';
	echo elgg_view('output/url', array(
		'text' => elgg_echo('attachments:upload'),
		'href' => '#',
		'class' => 'attachments-toggler',
	));
}

echo elgg_format_element('div', array(
	'class' => $class,
		), $uploads_form);

// @todo: update in Elgg 2.2 to use elgg_require_js()
?>
<script>require(['input/attachments']);</script>