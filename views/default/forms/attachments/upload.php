<?php

/**
 * Display attachment upload form
 *
 * @uses $vars['entity'] Entity to make attachments to
 */
$entity = elgg_extract('entity', $vars);
if (!$entity) {
	return;
}
echo elgg_view('input/hidden', [
	'name' => 'guid',
	'value' => $entity->guid,
]);
?>
<div class="elgg-field">
	<label class="elgg-field-label"><?= elgg_echo('attachments:files') ?></label>
	<?php
	echo elgg_view('input/attachments');
	?>
</div>
<div class="elgg-foot">
	<?php
	echo elgg_view('input/submit', [
		'value' => elgg_echo('attachments:attach'),
	]);
	?>
</div>
<script>require(['forms/attachments/upload']);</script>