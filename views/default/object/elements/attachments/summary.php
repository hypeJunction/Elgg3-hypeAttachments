
<?php

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof ElggEntity) {
	return;
}

$count = hypeapps_has_attachments($entity);
if (!$count) {
	return;
}

$link = elgg_view('output/url', [
	'text' => elgg_echo('attachments:count', [$count]),
	'href' => "attachments/view/$entity->guid",
	'class' => 'elgg-lightbox',
	'data-colorbox-opts' => json_encode([
		'maxWidth' => '600px',
		'maxHeight' => '400px',
	]),
		]);
?>
<div class="elgg-listing-summary-subtitle elgg-subtext"><?= $link ?></div>
