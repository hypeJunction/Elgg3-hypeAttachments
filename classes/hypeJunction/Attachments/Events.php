<?php

namespace hypeJunction\Attachments;

use ElggBatch;
use ElggEntity;

/**
 * @access private
 */
final class Events {

	/**
	 * Add attachments when comment/discussion reply is saved
	 *
	 * @param string     $event  "create"|"update"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function saveCommentAttachments($event, $type, $entity) {

		$subtype = $entity->getSubtype();
		if (!in_array($subtype, ['comment', 'discussion_reply'])) {
			return;
		}

		$attachments = hypeapps_attach_uploaded_files($entity, 'comment_attachments', [
			'access_id' => $entity->access_id,
			'container_guid' => $entity->guid,
		]);

		if ($attachments) {
			foreach ($attachments as $attachment) {
				$attachment->origin = 'attachments';
			}
		}
	}

	/**
	 * Add attachments when message is saved
	 *
	 * @param string     $event  "create"|"update"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function saveMessageAttachments($event, $type, $entity) {

		static $attachments;

		$subtype = $entity->getSubtype();
		if (!in_array($subtype, ['messages'])) {
			return;
		}

		$ids = array_merge([$entity->toId], (array) $entity->fromId);
		$acl_id = \hypeJunction\Access\Collection::create($ids)->getCollectionId();

		$ia = elgg_set_ignore_access(true);

		if (!isset($attachments)) {
			$attachments = hypeapps_attach_uploaded_files($entity, 'message_attachments', [
				'access_id' => $acl_id,
				'owner_guid' => $entity->fromId,
				'container_guid' => $entity->fromId,
			]);

			if ($attachments) {
				foreach ($attachments as $attachment) {
					$attachment->origin = 'attachments';
				}
			}
		} else if (!empty($attachments)) {
			foreach ($attachments as $attachment) {
				hypeapps_attach($entity, $attachment);
			}
		}


		elgg_set_ignore_access($ia);
	}

	/**
	 * Update attachment access to match that of the subject
	 *
	 * @param string     $event  'update:after'
	 * @param string     $type   'object'
	 * @param ElggEntity $entity The updated entity
	 * @return void
	 */
	public static function syncAttachmentAccess($event, $type, $entity) {
		if (!$entity instanceof ElggEntity) {
			return;
		}

		$ia = elgg_set_ignore_access(true);
		$options = array(
			'type' => 'object',
			'subtype' => 'file',
			'container_guid' => $entity->guid, // uploaded attachments are contained by the entity
			'metadata_name_value_pairs' => [
				'name' => 'origin',
				'value' => 'attachments',
			],
			'wheres' => array(
				"e.access_id != {$entity->access_id}"
			),
			'limit' => 0,
		);

		$batch = new ElggBatch('elgg_get_entities_from_metadata', $options, null, 25, false);
		foreach ($batch as $attachment) {
			// Update comment access_id
			$attachment->access_id = $entity->access_id;
			$attachment->save();
		}

		elgg_set_ignore_access($ia);
	}

}
