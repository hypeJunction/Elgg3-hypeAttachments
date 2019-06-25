<?php

namespace hypeJunction\Attachments;

class AddAttachmentsModule {

	/**
	 * Add slug field
	 *
	 * @param \Elgg\Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Hook $hook) {

		$entity = $hook->getEntityParam();
		$value = $hook->getValue();


		if (hypeapps_allow_attachments($entity->type, $entity->subtype)) {
			$value['attachments'] = [
				'enabled' => true,
				'position' => 'sidebar',
				'priority' => 300,
				'view' => 'post/modules/attachments',
				'label' => elgg_echo('attachments:title'),
			];
		}

		return $value;
	}
}
