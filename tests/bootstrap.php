<?php

$plugin_root = dirname(dirname(__FILE__));
$install_root = dirname(dirname($plugin_root));

require_once "$plugin_root/autoloader.php";
$bootstrap = "$plugin_root/vendor/elgg/elgg/engine/tests/phpunit/bootstrap.php";
$bootstrap_alt = "$install_root/vendor/elgg/elgg/engine/tests/phpunit/bootstrap.php";

if (file_exists($bootstrap)) {
	if (!is_dir("$plugin_root/tests/phpunit/tmp")) {
		mkdir("$plugin_root/tests/phpunit/tmp");
	}
	if (!file_exists("$plugin_root/tests/phpunit/tmp/bootstrap.php")) {
		copy($bootstrap, "$plugin_root/tests/phpunit/tmp/bootstrap.php");
	}
	require_once "$plugin_root/tests/phpunit/tmp/bootstrap.php";
} else {
	require_once "$install_root/vendor/elgg/elgg/engine/tests/phpunit/bootstrap.php";
}
