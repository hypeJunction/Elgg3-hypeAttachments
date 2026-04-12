<?php
/**
 * PHPUnit bootstrap for hypeAttachments plugin tests.
 * Plugin expected at {elgg_root}/mod/hypeattachments/
 */

// tests/ -> plugin/ -> mod/ -> elgg_root/
$elggRoot = dirname(__DIR__, 3);

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test base classes from the core package
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
	$file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
});

\Elgg\Application::loadCore();

// Ensure plugin helper functions are loaded even if the test snapshot DB
// hasn't marked the plugin active.
$pluginLib = dirname(__DIR__) . '/lib/functions.php';
if (file_exists($pluginLib)) {
	require_once $pluginLib;
}
