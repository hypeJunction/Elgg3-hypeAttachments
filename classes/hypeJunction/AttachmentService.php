<?php

namespace hypeJunction;

/**
 * Attachment Service
 * @access private
 */
class AttachmentService {

	/**
	 * Attach uploaded files for an entity
	 *
	 * @param \ElggEntity $entity     Entity to which the files are attached
	 * @param string      $input_name Form input name
	 * @param array       $attributes Metadata and attributes to set on each uploaded file
	 *                                This can include container_guid, origin etc
	 * @return int[] GUIDs of attached file entities
	 */
	public function attachUploadedFiles(\ElggEntity $entity, $input_name, array $attributes = []) {

		$upload_guids = (array) get_input($input_name, []);

		// files being uploaded via $_FILES
		$uploads = \hypeJunction\Filestore\UploadHandler::handle($input_name);
		if ($uploads) {
			foreach ($uploads as $upload) {
				if ($upload->guid) {
					$upload_guids[] = $upload->guid;
				}
			}
		}

		$result = [];
		if (empty($upload_guids)) {
			return $result;
		}

		foreach ($upload_guids as $upload_guid) {
			$upload = get_entity($upload_guid);
			if (!$upload) {
				continue;
			}
			foreach ($attributes as $key => $value) {
				$upload->$key = $value;
			}
			if ($upload->save() && $this->attach($entity, $upload)) {
				$result[] = $upload->guid;
			}
		}

		return $result;
	}

	/**
	 * Attach attachments to an entity
	 *
	 * @param \ElggEntity $entity     Subject entity
	 * @param \ElggFile   $attachment Attachment entity
	 * @return bool
	 */
	public function attach(\ElggEntity $entity, \ElggEntity $attachment) {
		return $entity->addRelationship($attachment->guid, 'attached');
	}

	/**
	 * Detach attachments from entity
	 *
	 * @param \ElggEntity $entity     Subject entity
	 * @param \ElggFile   $attachment Attached entity
	 * @param bool        $delete     Also delete attached entities
	 * @return bool
	 */
	public function detach(\ElggEntity $entity, \ElggEntity $attachment, $delete = false) {
		if ($delete) {
			// This will check delete permissions
			return $attachment->delete();
		}

		return $entity->removeRelationship($attachment->guid, 'attached');
	}

	/**
	 * Returns an array of attached entities
	 *
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return \ElggEntity[]|false
	 */
	public function getAttachments(\ElggEntity $entity, array $options = array()) {
		$options = $this->getAttachmentsFilterOptions($entity, $options);
		return elgg_get_entities_from_relationship($options);
	}

	/**
	 * Check if entity has attachments
	 * Returns a count of attachments
	 *
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return int
	 */
	public function hasAttachments(\ElggEntity $entity, array $options = array()) {
		$options['count'] = true;
		return $this->getAttachments($entity, $options);
	}

	/**
	 * Returns getter options for comment attachments
	 *
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return array
	 */
	protected function getAttachmentsFilterOptions(\ElggEntity $entity, array $options = array()) {
		$defaults = array(
			'relationship' => 'attached',
			'relationship_guid' => (int) $entity->guid,
			'inverse_relationship' => false,
		);
		return array_merge($defaults, $options);
	}

}
