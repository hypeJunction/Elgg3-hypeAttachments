<?php

namespace hypeJunction\Attachments;

use ElggEntity;
use ElggMenuItem;

/**
 * @access private
 */
final class Menus {

	/**
	 * Setup entity menu
	 *
	 * @param string         $hook   "register"
	 * @param string         $type   "menu:entity"
	 * @param ElggMenuItem[] $return Menu
	 * @param array          $params Hook params
	 * @return ElggMenuItem[]
	 */
	public static function setupEntityMenu($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if (!$entity instanceof ElggEntity || !$entity->canEdit()) {
			return;
		}

		$subject_guid = $entity->getVolatileData('attachment_subject');
		$subject = get_entity($subject_guid);

		if ($subject instanceof ElggEntity) {

			foreach ($return as $key => $item) {
				if ($item instanceof ElggMenuItem && $item->getName() == 'delete') {
					$priority = $item->getPriority();
					unset($return[$key]);
				}
			}

			if ($entity->container_guid == $subject->guid) {
				// Attachment has been made when the entity was created, so it can be deleted
				$delete = true;
				$title = elgg_echo('delete');
			} else {
				// Attachment has been added externally, do not delete it
				$delete = false;
				$title = elgg_echo('attachments:detach');
			}

			$return[] = ElggMenuItem::factory([
				'name' => 'delete',
				'text' => elgg_view_icon('delete'),
				'href' => elgg_http_add_url_query_elements('action/attachments/detach', [
					'guid' => $subject->guid,
					'attachment_guid' => $entity->guid,
					'delete' => $delete,
				]),
				'is_action' => true,
				'confirm' => true,
				'link_class' => 'attachments-detach-action',
				'title' => $title,
				'priority' => $priority,
			]);
		}

		if (hypeapps_allow_attachments($entity->type, $entity->getSubtype())) {
			$return[] = ElggMenuItem::factory([
				'name' => 'attach',
				'text' => elgg_echo('attachments:upload'),
				'href' => "attachments/upload/$entity->guid",
				'link_class' => 'elgg-lightbox',
				'data-colorbox-opts' => json_encode([
					'maxWidth' => '600px',
				]),
				'deps' => ['elgg/lightbox'],
			]);
		}

		return $return;
	}

}
