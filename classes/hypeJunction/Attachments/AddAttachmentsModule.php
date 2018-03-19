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

		$value = $hook->getValue();

		$value['attachments'] = [
			'enabled' => true,
			'position' => 'sidebar',
			'priority' => 300,
		];

		return $value;
	}
}
