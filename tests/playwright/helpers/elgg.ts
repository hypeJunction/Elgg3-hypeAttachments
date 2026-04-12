import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(
  page: Page,
  username: string = 'admin',
  password: string = 'admin12345'
) {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[name="username"]', { timeout: 15000 });
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.click('input[type="submit"], button[type="submit"]'),
  ]);
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  try {
    const [rows] = await conn.execute(sql, params);
    return rows as any[];
  } finally {
    await conn.end();
  }
}

export async function pluginIsActive(pluginId: string): Promise<boolean> {
  const rows = await queryDb(
    `SELECT e.guid FROM elgg_entities e
       JOIN elgg_metadata m ON m.entity_guid = e.guid
      WHERE e.type = 'object'
        AND e.subtype = 'plugin'
        AND m.name = 'title'
        AND m.value = ?
        AND e.enabled = 'yes'`,
    [pluginId]
  );
  return rows.length > 0;
}
