<?php

namespace hypeJunction\Attachments;

use Elgg\Request;
use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class AttachmentsField extends Field {

	/**
	 * {@inheritdoc}
	 */
	public function raw(Request $request, ElggEntity $entity) {
		return elgg_get_uploaded_files($this->name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value) {
		parent::validate($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(ElggEntity $entity, ParameterBag $parameters) {
		return hypeapps_attach_uploaded_files($entity, 'attachments', [
			'access_id' => $entity->access_id,
			'container_guid' => $entity->guid,
			'origin' => 'cms',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieve(ElggEntity $entity) {
		return hypeapps_get_attachments($entity);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible(ElggEntity $entity, $context = null) {
		if (!hypeapps_allow_attachments($entity->type, $entity->subtype)) {
			return false;
		}

		return parent::isVisible($entity, $context); // TODO: Change the autogenerated stub
	}
}