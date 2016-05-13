<?php

$guid = get_input('guid');
$entity = get_entity($guid);

$attachment_guid = get_input('attachment_guid');
$attachment = get_entity($attachment_guid);

$delete = get_input('delete', false);

if ($entity && $entity->canEdit() && $attachment && hypeapps_detach($entity, $attachment, $delete)) {
	system_message(elgg_echo('attachments:detach:success'));
} else {
	register_error(elgg_echo('attachments:detach:error'));
}