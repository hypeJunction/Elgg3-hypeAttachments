<?php

$guid = get_input('guid');
$entity = get_entity($guid);

$attachment_guid = get_input('attachment_guid');
$attachment = get_entity($attachment_guid);

$delete = get_input('delete', false);

if ($entity
		&& $entity->canEdit()
		&& $attachment
		&& hypeapps_detach($entity, $attachment, $delete)
	) {
	elgg_register_success_message(elgg_echo('attachments:detach:success'));
} else {
	elgg_register_error_message(elgg_echo('attachments:detach:error'));
}
