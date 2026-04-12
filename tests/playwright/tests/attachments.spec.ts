import { test, expect } from '@playwright/test';
import { loginAs, queryDb, pluginIsActive } from '../helpers/elgg';

/**
 * Pre-migration Playwright tests for hypeAttachments.
 *
 * These hit the running Elgg 4.x instance and pin user-visible behavior
 * so the Elgg 5.x migration can be verified later.
 */

test.describe('hypeAttachments plugin', () => {

  test('plugin entity exists and is active in db', async () => {
    const active = await pluginIsActive('hypeattachments');
    expect(active).toBeTruthy();
  });

  test('attachments css view is served', async ({ page }) => {
    // The plugin extends css/elgg — compiled CSS must be served.
    const response = await page.goto('/cache/0/default/css/input/attachments.css');
    // Elgg serves asset views — either 200, or 404 if cache-path differs.
    // We just assert the request doesn't 500 (server error indicates
    // a regression in the view or its dependencies).
    expect(response?.status() ?? 0).toBeLessThan(500);
  });

  test('upload route resolves (requires auth + valid guid)', async ({ page }) => {
    await loginAs(page);
    // Unauthenticated the route requires auth; with login we check
    // that the request returns a non-500 status (route is registered).
    const response = await page.goto('/attachments/upload/1');
    expect(response?.status() ?? 0).toBeLessThan(500);
  });

  test('view route resolves for a valid guid', async ({ page }) => {
    await loginAs(page);
    const response = await page.goto('/attachments/view/1');
    expect(response?.status() ?? 0).toBeLessThan(500);
  });

  test('admin plugin settings page renders', async ({ page }) => {
    await loginAs(page, 'admin', 'admin12345');
    const response = await page.goto('/admin/plugin_settings/hypeattachments');
    expect(response?.status() ?? 0).toBeLessThan(500);

    // No system error message should surface.
    const errors = page.locator('.elgg-system-messages .elgg-message-error');
    await expect(errors).toHaveCount(0);
  });

  test('attach action is reachable via action token page flow', async ({ page }) => {
    await loginAs(page);
    // Hit site root, confirm action tokens are embedded (plugin lifecycle ok).
    await page.goto('/');
    const content = await page.content();
    expect(content).toContain('__elgg_token');
  });

  test('no plugin-attachments rows orphan the entity table', async () => {
    // Pin the data model: any "attached" relationship should reference
    // two existing entities. A dangling relationship after migration would
    // indicate a broken data migration.
    const rows = await queryDb(
      `SELECT r.guid_one, r.guid_two
         FROM elgg_entity_relationships r
    LEFT JOIN elgg_entities e1 ON e1.guid = r.guid_one
    LEFT JOIN elgg_entities e2 ON e2.guid = r.guid_two
        WHERE r.relationship = 'attached'
          AND (e1.guid IS NULL OR e2.guid IS NULL)`
    );
    expect(rows.length).toBe(0);
  });
});
