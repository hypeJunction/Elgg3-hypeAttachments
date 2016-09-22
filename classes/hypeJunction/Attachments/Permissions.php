<?php

namespace hypeJunction\Attachments;

/**
 * @access private
 */
final class Permissions {
	
	/**
	 * Check if attachments are allowed for this type by plugin settings
	 * 
	 * @param string $hook   "allow_attachments"
	 * @param string $type   "<entity_type>:<entity_subtype>"
	 * @param bool   $return If allowed
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function allowsAttachments($hook, $type, $return, $params) {

		list($entity_type, $entity_subtype) = explode(':', $type);

		// handle special cases
		if ($entity_type == 'object' && $entity_subtype == 'messages') {
			// users should not be able to attach new items after message has been sent
			return false;
		}
		
		if ((bool) elgg_get_plugin_setting("$entity_type:$entity_subtype", 'hypeAttachments')) {
			return true;
		}
	}

	/**
	 * Message attachments can not be changed
	 *
	 * @param string $hook   "permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function protectMessageAttachments($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		$ia = elgg_set_ignore_access(true);

		$messages = elgg_get_entities_from_relationship([
			'types' => 'object',
			'subtypes' => 'messages',
			'relationship' => 'attached',
			'relationship_guid' => (int) $entity->guid,
			'inverse_relationship' => true,
			'count' => true,
		]);

		elgg_set_ignore_access($ia);

		if ($messages > 1) {
			// if this entity is attached to more than 1 message,
			// do not allow it to be edited or deleted
			return false;
		}
	}
}
