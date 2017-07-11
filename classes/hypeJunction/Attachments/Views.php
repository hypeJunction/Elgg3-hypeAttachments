<?php

namespace hypeJunction\Attachments;

/**
 * @access private
 */
final class Views {

	/**
	 * Appends attachment count information to the summary subtitle
	 * 
	 * @param string $hook   "view_vars"
	 * @param string $type   "object/elements/summary"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function filterSummaryVars($hook, $type, $return, $params) {

		if (elgg_is_active_plugin('hypeUI')) {
			return;
		}

		$entity = elgg_extract('entity', $return);
		if (!$entity) {
			return;
		}

		$attachments = elgg_view('object/elements/attachments/summary', [
			'entity' => $entity,
		]);

		if (!$attachments) {
			return;
		}

		$subtitle = elgg_extract('subtitle', $return, '');
		if (!$subtitle) {
			$subtitle = $attachments;
		} else {
			$subtitle .= '<br />' . $attachments;
		}
		$return['subtitle'] = $subtitle;
		
		return $return;
	}

	/**
	 * Appends attachment count information to the summary subtitle
	 *
	 * @param string $hook   "view_vars"
	 * @param string $type   "object/elements/full"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function filterFullViewVars($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $return);
		if (!$entity) {
			return;
		}

		$attachments = elgg_view('object/elements/attachments/full', [
			'entity' => $entity,
		]);

		if (!$attachments) {
			return;
		}

		if (elgg_is_active_plugin('hypeUI')) {
			$return['attachments'] .= $attachments;
		} else {
			$body = elgg_extract('body', $return, '');
			if (!$body) {
				$body = $attachments;
			} else {
				$body .= '<br />' . $attachments;
			}
			$return['body'] = $body;
		}

		return $return;
	}

}
