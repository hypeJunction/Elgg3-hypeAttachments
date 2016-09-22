<?php

use hypeJunction\AttachmentService;

/**
 * Attach uploaded files for an entity
 *
 * @param ElggEntity $entity     Entity to which the files are attached
 * @param string      $input_name Form input name
 * @param array       $attributes Metadata and attributes to set on each uploaded file
 *                                This can include container_guid, origin etc
 * @return ElggFile[] Attached file entities
 */
function hypeapps_attach_uploaded_files(ElggEntity $entity, $input_name, array $attributes = []) {
	$service = AttachmentService::getInstance();
	return $service->attachUploadedFiles($entity, $input_name, $attributes);
}

/**
 * Attach attachments to an entity
 *
 * @param ElggEntity $entity     Subject entity
 * @param ElggFile   $attachment Attachment entity
 * @return bool
 */
function hypeapps_attach(ElggEntity $entity, ElggEntity $attachment) {
	$service = AttachmentService::getInstance();
	return $service->attach($entity, $attachment);
}

/**
 * Detach attachments from entity
 *
 * @param ElggEntity $entity     Subject entity
 * @param ElggFile   $attachment Attached entity
 * @param bool        $delete     Also delete attached entities
 * @return bool
 */
function hypeapps_detach(ElggEntity $entity, ElggEntity $attachment, $delete = false) {
	$service = AttachmentService::getInstance();
	return $service->detach($entity, $attachment, $delete);
}

/**
 * Returns an array of attached entities
 *
 * @param ElggEntity $entity  Subject entity
 * @param array       $options Additional options
 * @return ElggEntity[]|false
 */
function hypeapps_get_attachments(ElggEntity $entity, array $options = array()) {
	$service = AttachmentService::getInstance();
	return $service->getAttachments($entity, $options);
}

/**
 * Check if entity has attachments
 * Returns a count of attachments
 *
 * @param ElggEntity $entity  Subject entity
 * @param array       $options Additional options
 * @return int
 */
function hypeapps_has_attachments(ElggEntity $entity, array $options = array()) {
	$service = AttachmentService::getInstance();
	return $service->hasAttachments($entity, $options);
}

/**
 * Check if attachments are allowed for this entity type and subtype
 *
 * @param string $type    Entity type
 * @param string $subtype Entity subtype
 * @return bool
 */
function hypeapps_allow_attachments($type, $subtype) {
	return (bool) elgg_trigger_plugin_hook('allow_attachments', "$type:$subtype", [], false);
}