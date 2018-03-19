<?php

namespace hypeJunction\Attachments;

use Elgg\Request;

class AddFormField {

	/**
	 * Add field
	 *
	 * @param \Elgg\Hook $hook Hook
	 *
	 * @return \ElggMenuItem[]|null
	 */
	public function __invoke(\Elgg\Hook $hook) {

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
			'#visibility' => function (\ElggEntity $entity) {
				return hypeapps_allow_attachments($entity->type, $entity->subtype);
			},
			'#profile' => false,
		];

		return $fields;
	}
}
