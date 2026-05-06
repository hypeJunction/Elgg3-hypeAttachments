<?php

namespace hypeJunction\Attachments;

use Elgg\Event;
use hypeJunction\Fields\Collection;
use InvalidParameterException;

/**
 * Adds the attachments field to a form fields collection
 */
class AddFormField {

	/**
	 * Add field
	 *
	 * @param Event $event Event
	 *
	 * @return Collection
	 * @throws InvalidParameterException
	 */
	public function __invoke(Event $event) {

		$fields = $event->getValue();
		/* @var $fields \hypeJunction\Fields\Collection */

		$fields->add('attachments', new AttachmentsField([
			'type' => 'attachments',
			'priority' => 700,
			'is_profile_field' => false,
			'is_export_field' => true,
		]));

		return $fields;
	}
}
