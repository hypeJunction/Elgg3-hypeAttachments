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

		if ((bool) elgg_get_plugin_setting("$entity_type:$entity_subtype", 'hypeAttachments')) {
			return true;
		}
	}
}
