<?php

namespace hypeJunction\Attachments;

/**
 * Registers the attachments module on entity views
 */
class AddAttachmentsModule {

	/**
	 * Add slug field
	 *
	 * @param \Elgg\Event $event Hook
	 *
	 * @return array
	 */
	public function __invoke(\Elgg\Event $event) {

		$entity = $event->getEntityParam();
		$value = $event->getValue();


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
