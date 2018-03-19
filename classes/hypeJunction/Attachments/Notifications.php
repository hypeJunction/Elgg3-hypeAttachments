<?php

namespace hypeJunction\Attachments;

use Elgg\Notifications\Notification;
use ElggEntity;

/**
 * @access private
 */
final class Notifications {

	/**
	 * Register notification hooks
	 *
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function registerNotificationHooks(ElggEntity $entity) {
		$subtype = $entity->getSubtype();
		elgg_register_notification_event('object', $subtype, ['attach']);

		$hook_type = "notification:attach:object:$subtype";
		$handler = [Notifications::class, 'prepareNotification'];
		elgg_unregister_plugin_hook_handler('prepare', $hook_type, $handler); // remove dupe
		elgg_register_plugin_hook_handler('prepare', $hook_type, $handler);
	}

	/**
	 * Prepare a notification message about new attachments
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function prepareNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();
		$actor = $params['event']->getActor();
		$language = $params['language'];

		$attachments_list = [];
		$attachments = hypeapps_get_attachments($entity, ['limit' => 0]);
		foreach ($attachments as $attachment) {
			$attachments_list[] = $attachment->getDisplayName() . ' (' . elgg_view('output/url', [
				'text' => elgg_echo('view'),
				'href' => $attachment->getURL(),
			]) . ')';
		}

		$notification->subject = elgg_echo('attachments:notify:subject', [$entity->title], $language);
		$notification->body = elgg_echo('attachments:notify:body', [
			$actor->name,
			$entity->title,
			implode(PHP_EOL, $attachments_list),
			$entity->getURL(),
		], $language);
		$notification->summary = elgg_echo('attachments:notify:summary', [$entity->title], $language);

		return $notification;
	}

}
