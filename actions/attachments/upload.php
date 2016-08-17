<?php

$guid = get_input('guid');
elgg_entity_gatekeeper($guid);

$entity = get_entity($guid);

if (!$entity->canEdit() || !hypeapps_allow_attachments($entity->getType(), $entity->getSubtype())) {
	register_error(elgg_echo('actionnotauthorized'));
	forward(REFERRER);
}

$result = hypeapps_attach_uploaded_files($entity, 'uploads', [
	'access_id' => $entity->access_id,
	'container_guid' => $entity->guid,
		]);

if (empty($result)) {
	register_error(elgg_echo('attachments:upload:empty'));
} else {
	system_message(elgg_echo('attachments:upload:success', [count($result)]));

	$attachments = [];
	foreach ($result as $attachment) {
		$attachment->origin = 'attachments';
		$attachments[] = $attachment->guid;
	}

	elgg_trigger_event('attach', 'object', $entity);
	
	if (elgg_is_xhr()) {
		echo json_encode([
			'attachments' => $attachments,
		]);
	}
}

forward($entity->getURL());
