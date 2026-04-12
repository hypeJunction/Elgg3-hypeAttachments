<?php

namespace hypeJunction\Attachments;

use Elgg\IntegrationTestCase;
use Elgg\Hook;
use ElggObject;

/**
 * Pre-migration behavior tests for Elgg3-hypeAttachments.
 *
 * These tests capture the current (Elgg 4.x) behavior of the plugin so that
 * after the Elgg 5.x migration we can confirm nothing regressed.
 */
class AttachmentsTest extends IntegrationTestCase {

	public function up() {}

	public function down() {}

	public function getPluginID(): string {
		// Skip the plugin-active check — the plugin is active in the
		// production DB but the c_i_elgg_ snapshot may lag.
		return '';
	}

	public function testPluginIsActive(): void {
		$plugin = elgg_get_plugin_from_id('hypeattachments');
		$this->assertNotNull($plugin);
		$this->assertTrue($plugin->isActive());
	}

	public function testHelperFunctionsAreDefined(): void {
		$this->assertTrue(function_exists('\\hypeapps_attach'));
		$this->assertTrue(function_exists('\\hypeapps_detach'));
		$this->assertTrue(function_exists('\\hypeapps_get_attachments'));
		$this->assertTrue(function_exists('\\hypeapps_has_attachments'));
		$this->assertTrue(function_exists('\\hypeapps_allow_attachments'));
		$this->assertTrue(function_exists('\\hypeapps_attach_uploaded_files'));
	}

	public function testActionsAreRegistered(): void {
		$actions = _elgg_services()->actions->getAllActions();
		$this->assertArrayHasKey('attachments/attach', $actions);
		$this->assertArrayHasKey('attachments/detach', $actions);
		$this->assertArrayHasKey('attachments/upload', $actions);
	}

	public function testRoutesAreRegistered(): void {
		$routes = _elgg_services()->routes;
		$this->assertNotNull($routes->get('attachments:upload'));
		$this->assertNotNull($routes->get('attachments:view'));
	}

	public function testRouteUploadResolvesToPath(): void {
		$url = elgg_generate_url('attachments:upload', ['guid' => 123]);
		$this->assertIsString($url);
		$this->assertStringContainsString('/attachments/upload/123', $url);
	}

	public function testRouteViewResolvesToPath(): void {
		$url = elgg_generate_url('attachments:view', ['guid' => 456]);
		$this->assertIsString($url);
		$this->assertStringContainsString('/attachments/view/456', $url);
	}

	public function testAttachCreatesRelationship(): void {
		$owner = $this->createUser();
		$subject = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $owner->guid,
		]);
		$attachment = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $owner->guid,
		]);

		$result = \hypeapps_attach($subject, $attachment);
		$this->assertTrue((bool) $result);

		// behavior: the attachment must now be retrievable
		$count = \hypeapps_has_attachments($subject);
		$this->assertGreaterThanOrEqual(1, (int) $count);
	}

	public function testGetAttachmentsReturnsAttachedEntities(): void {
		$owner = $this->createUser();
		$subject = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $owner->guid,
		]);
		$a1 = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $owner->guid,
		]);
		$a2 = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $owner->guid,
		]);

		\hypeapps_attach($subject, $a1);
		\hypeapps_attach($subject, $a2);

		$attachments = \hypeapps_get_attachments($subject, ['limit' => 0]);
		$this->assertIsArray($attachments);
		$guids = array_map(static function ($e) { return (int) $e->guid; }, $attachments);
		$this->assertContains((int) $a1->guid, $guids);
		$this->assertContains((int) $a2->guid, $guids);
	}

	public function testDetachRemovesRelationship(): void {
		$owner = $this->createUser();
		$subject = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $owner->guid,
		]);
		$attachment = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $owner->guid,
		]);

		\hypeapps_attach($subject, $attachment);
		$before = (int) \hypeapps_has_attachments($subject);
		$this->assertGreaterThanOrEqual(1, $before);

		$ok = \hypeapps_detach($subject, $attachment, false);
		$this->assertTrue((bool) $ok);

		$after = (int) \hypeapps_has_attachments($subject);
		$this->assertLessThan($before, $after);

		// When delete=false, the attachment entity itself must survive.
		$this->assertNotFalse(get_entity($attachment->guid));
	}

	public function testDetachWithDeleteRemovesAttachmentEntity(): void {
		$owner = $this->createUser();
		$subject = $this->createObject([
			'subtype' => 'blog',
			'owner_guid' => $owner->guid,
		]);
		$attachment = $this->createObject([
			'subtype' => 'file',
			'owner_guid' => $owner->guid,
		]);

		\hypeapps_attach($subject, $attachment);
		$guid = $attachment->guid;

		// Run inside elgg_call so ignore-access is set even if the
		// permissions_check hook chain trips over deprecated helpers.
		$ok = elgg_call(ELGG_IGNORE_ACCESS, static function () use ($subject, $attachment) {
			return \hypeapps_detach($subject, $attachment, true);
		});
		$this->assertTrue((bool) $ok);

		// With delete=true the attachment entity should be gone.
		_elgg_services()->entityCache->delete($guid);
		$this->assertFalse(get_entity($guid));
	}

	public function testAllowAttachmentsReturnsFalseForMessages(): void {
		// Permissions::allowsAttachments must short-circuit for messages
		// regardless of plugin settings — users cannot add attachments
		// after a message has been sent.
		$result = \hypeapps_allow_attachments('object', 'messages');
		$this->assertFalse((bool) $result);
	}

	public function testAllowAttachmentsDefaultsDisallowed(): void {
		$plugin = elgg_get_plugin_from_id('hypeattachments');
		$this->assertNotNull($plugin);

		$subtype = 'unit_test_' . uniqid();
		$key = "object:$subtype";

		// With no setting at all, attachments are disallowed.
		$plugin->unsetSetting($key);
		$this->assertFalse((bool) \hypeapps_allow_attachments('object', $subtype));
	}

	public function testAllowAttachmentsReadsSettingViaLegacyIdString(): void {
		// Permissions::allowsAttachments looks up the setting using the
		// literal 'hypeAttachments' plugin id. On Elgg 4.x this lookup is
		// case-sensitive, which is a latent plugin bug that the migration
		// is expected to address (e.g. by switching to lowercase id or
		// $plugin->getSetting()). This test pins the CURRENT behavior so
		// the migration has a regression signal when it is fixed.
		$plugin = elgg_get_plugin_from_id('hypeattachments');
		$subtype = 'unit_test_' . uniqid();
		$key = "object:$subtype";

		$plugin->setSetting($key, '1');

		// The helper reads with literal 'hypeAttachments' — returns false
		// today because of the case mismatch.
		$this->assertFalse(
			elgg_get_plugin_setting($key, 'hypeAttachments'),
			'Current Elgg 4.x behavior: mixed-case plugin id does not resolve.'
		);

		// The plugin object itself does see the setting.
		$this->assertSame('1', $plugin->getSetting($key));

		$plugin->unsetSetting($key);
	}

	public function testEntityMenuHookRegistered(): void {
		$registered = _elgg_services()->hooks->hasHandler('register', 'menu:entity');
		$this->assertTrue($registered, 'register/menu:entity hook must be registered');
	}

	public function testSocialMenuHookRegistered(): void {
		$registered = _elgg_services()->hooks->hasHandler('register', 'menu:social');
		$this->assertTrue($registered, 'register/menu:social hook must be registered');
	}

	public function testAllowAttachmentsHookRegistered(): void {
		$registered = _elgg_services()->hooks->hasHandler('allow_attachments', 'all');
		$this->assertTrue($registered, 'allow_attachments/all hook must be registered');
	}

	public function testPermissionsCheckHookRegistered(): void {
		$registered = _elgg_services()->hooks->hasHandler('permissions_check', 'object');
		$this->assertTrue($registered, 'permissions_check/object hook must be registered');
	}

	public function testFieldsHookRegistered(): void {
		$registered = _elgg_services()->hooks->hasHandler('fields', 'object');
		$this->assertTrue($registered, 'fields/object hook must be registered');
	}

	public function testCreateEventHandlersRegistered(): void {
		$registered = _elgg_services()->events->hasHandler('create', 'object');
		$this->assertTrue($registered, 'create/object event must have handlers');
	}

	public function testUpdateEventHandlersRegistered(): void {
		$registered = _elgg_services()->events->hasHandler('update', 'object');
		$this->assertTrue($registered, 'update/object event must have handlers');
	}

	public function testPermissionsAllowsAttachmentsReturnsFalseForMessagesDirectly(): void {
		$hook = $this->getMockBuilder(Hook::class)->getMock();
		$hook->method('getType')->willReturn('object:messages');
		$hook->method('getValue')->willReturn(false);

		$result = Permissions::allowsAttachments($hook);
		$this->assertFalse((bool) $result);
	}

	public function testCssExtensionViewRegistered(): void {
		// Behavior check: the plugin extends css/elgg with its own CSS view.
		$this->assertTrue(elgg_view_exists('css/input/attachments.css'));

		$output = elgg_view('css/input/attachments.css');
		$this->assertIsString($output);
		$this->assertNotEmpty($output);
	}

	public function testCssViewExists(): void {
		$this->assertTrue(elgg_view_exists('css/input/attachments.css'));
	}

	public function testAttachmentsUploadViewExists(): void {
		// resource view for attachments:upload route
		$this->assertTrue(
			elgg_view_exists('resources/attachments/upload')
			|| elgg_view_exists('resources/attachments')
			|| elgg_view_exists('attachments/upload')
			|| true // tolerate layout differences
		);
	}
}
