<?php

/**
 * Attachments
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2016, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'hypeapps_attachments_init');

/**
 * Initialize
 * @return void
 */
function hypeapps_attachments_init() {

	elgg_extend_view('elgg.css', 'input/attachments.css');

	elgg_register_action('attachments/attach', __DIR__ . '/actions/attachments/attach.php');
	elgg_register_action('attachments/detach', __DIR__ . '/actions/attachments/detach.php');
}

/**
 * Attach uploaded files for an entity
 *
 * @param \ElggEntity $entity     Entity to which the files are attached
 * @param string      $input_name Form input name
 * @param array       $attributes Metadata and attributes to set on each uploaded file
 *                                This can include container_guid, origin etc
 * @return int[] GUIDs of attached file entities
 */
function hypeapps_attach_uploaded_files(\ElggEntity $entity, $input_name, array $attributes = []) {
	$service = new \hypeJunction\AttachmentService();
	return $service->attachUploadedFiles($entity, $input_name, $attributes);
}

/**
 * Attach attachments to an entity
 *
 * @param \ElggEntity $entity     Subject entity
 * @param \ElggFile   $attachment Attachment entity
 * @return bool
 */
function hypeapps_attach(\ElggEntity $entity, \ElggEntity $attachment) {
	$service = new \hypeJunction\AttachmentService();
	return $service->attach($entity, $attachment);
}

/**
 * Detach attachments from entity
 *
 * @param \ElggEntity $entity     Subject entity
 * @param \ElggFile   $attachment Attached entity
 * @param bool        $delete     Also delete attached entities
 * @return bool
 */
function hypeapps_detach(\ElggEntity $entity, \ElggEntity $attachment, $delete = false) {
	$service = new \hypeJunction\AttachmentService();
	return $service->detach($entity, $attachment, $delete);
}

/**
 * Returns an array of attached entities
 *
 * @param \ElggEntity $entity  Subject entity
 * @param array       $options Additional options
 * @return \ElggEntity[]|false
 */
function hypeapps_get_attachments(\ElggEntity $entity, array $options = array()) {
	$service = new \hypeJunction\AttachmentService();
	return $service->getAttachments($entity, $options);
}

/**
 * Check if entity has attachments
 * Returns a count of attachments
 *
 * @param \ElggEntity $entity  Subject entity
 * @param array       $options Additional options
 * @return int
 */
function hypeapps_has_attachments(\ElggEntity $entity, array $options = array()) {
	$service = new \hypeJunction\AttachmentService();
	return $service->hasAttachments($entity, $options);
}
