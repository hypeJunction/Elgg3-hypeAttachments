<?php

namespace hypeJunction\Attachments;

use Elgg\Database\QueryBuilder;
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
	 *
	 * @return void
	 */
	public static function saveCommentAttachments(\Elgg\Event $event) {

		$subtype = $event->getObject()->getSubtype();
		if (!in_array($subtype, ['comment', 'discussion_reply'])) {
			return;
		}

		$attachments = hypeapps_attach_uploaded_files($event->getObject(), 'comment_attachments', [
			'access_id' => $event->getObject()->access_id,
			'container_guid' => $event->getObject()->guid,
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
	 *
	 * @return void
	 */
	public static function saveMessageAttachments(\Elgg\Event $event) {

		static $attachments;

		$subtype = $event->getObject()->getSubtype();
		if (!in_array($subtype, ['messages'])) {
			return;
		}

		$ids = array_merge([$event->getObject()->toId], (array) $event->getObject()->fromId);
		$acl_id = \hypeJunction\Access\Collection::create($ids)->getCollectionId();

		$ia = elgg_set_ignore_access(true);

		if (!isset($attachments)) {
			$attachments = hypeapps_attach_uploaded_files($event->getObject(), 'message_attachments', [
				'access_id' => $acl_id,
				'owner_guid' => $event->getObject()->fromId,
				'container_guid' => $event->getObject()->fromId,
			]);

			if ($attachments) {
				foreach ($attachments as $attachment) {
					$attachment->origin = 'attachments';
				}
			}
		} else if (!empty($attachments)) {
			foreach ($attachments as $attachment) {
				hypeapps_attach($event->getObject(), $attachment);
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
	 *
	 * @return void
	 */
	public static function syncAttachmentAccess(\Elgg\Event $event) {
		if (!$event->getObject() instanceof ElggEntity) {
			return;
		}

		$ia = elgg_set_ignore_access(true);
		$options = [
			'type' => 'object',
			'subtype' => 'file',
			'container_guid' => $event->getObject()->guid, // uploaded attachments are contained by the entity
			'metadata_name_value_pairs' => [
				[
					'name' => 'origin',
					'value' => 'attachments',
				],
			],
			'wheres' => [
				function(QueryBuilder $qb) use ($event->getObject()) {
					return $qb->compare('e.access_id', '!=', (int) $event->getObject()->access_id, ELGG_VALUE_INTEGER);
				}
			],
			'limit' => 0,
			'batch' => true,
		];

		$attachments = elgg_get_entities($options);
		foreach ($attachments as $attachment) {
			// Update comment access_id
			$attachment->access_id = $event->getObject()->access_id;
			$attachment->save();
		}

		elgg_set_ignore_access($ia);
	}

}
