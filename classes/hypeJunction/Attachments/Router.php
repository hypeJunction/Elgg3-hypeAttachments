<?php

namespace hypeJunction\Attachments;

/**
 * @access private
 */
final class Router {

	/**
	 * Attachments page handler
	 *
	 * @param array $segments URL segments
	 * @return bool
	 */
	public function route($segments) {

		$page = array_shift($segments);

		switch ($page) {
			case 'upload' :
				echo elgg_view('resources/attachments/upload', [
					'guid' => array_shift($segments),
				]);
				return true;

			case 'view' :
				echo elgg_view('resources/attachments/view', [
					'guid' => array_shift($segments),
				]);
				return true;
		}

		return false;
	}

}
