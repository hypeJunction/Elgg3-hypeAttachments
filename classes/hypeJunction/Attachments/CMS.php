<?php

namespace hypeJunction\Attachments;

use Elgg\Event;

/**
 * Integrates attachments with the CMS form fields collection
 */
class CMS {

	/**
	 * Add attachment field to CMS form
	 *
	 * @param Event $hook Event
	 * @return array
	 */
	public static function addAttachmentField(Event $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity || !hypeapps_allow_attachments($entity->type, $entity->subtype)) {
			return;
		}

		$fields = $hook->getValue();

		$fields['attachments'] = [
			'#type' => 'attachments',
			'#section' => 'content',
			'#priority' => 700,
			'#input' => function(Request $request) {
				return elgg_get_uploaded_files('attachments');
			},
			'#getter' => function(\ElggEntity $entity) {
				return hypeapps_get_attachments($entity);
			},
			'#setter' => function(\ElggEntity $entity) {
				return hypeapps_attach_uploaded_files($entity, 'attachments', [
					'access_id' => $entity->access_id,
					'container_guid' => $entity->guid,
					'origin' => 'cms',
				]);
			},
		];

		return $fields;
	}

	/**
	 * Remove attachments from profile fields
	 *
	 * @param Event $hook Event
	 *
	 * @return mixed
	 */
	public static function filterProfileFields(Event $hook) {

		$fields = $hook->getValue();

		foreach ($fields as $key => $field) {
			$name = elgg_extract('name', $field);
			if ($name == 'attachments') {
				unset($fields[$key]);
			}
		}

		return $fields;
	}
}
