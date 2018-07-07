<?php

namespace hypeJunction\Attachments;

use Elgg\Hook;
use hypeJunction\Fields\Collection;
use InvalidParameterException;

class AddFormField {

	/**
	 * Add field
	 *
	 * @param Hook $hook Hook
	 *
	 * @return Collection
	 * @throws InvalidParameterException
	 */
	public function __invoke(Hook $hook) {

		$fields = $hook->getValue();
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
