import { chromium, FullConfig } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { HPOS, wp } from './env';

/** Logs in once as admin and stores the session for all tests. */
export default async function globalSetup(config: FullConfig): Promise<void> {
  const { baseURL } = config.projects[0].use;
  mkdirSync('.auth', { recursive: true });
  const browser = await chromium.launch();
  const page = await browser.newPage({ baseURL });
  await page.goto('/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');
  await page.waitForURL(/wp-admin/);
  await page.context().storageState({ path: '.auth/admin.json' });
  await browser.close();

  // The matrix is only meaningful if WooCommerce really uses the storage this run is for.
  const usesHpos = wp('eval', 'echo \\Automattic\\WooCommerce\\Utilities\\OrderUtil::custom_orders_table_usage_is_enabled() ? 1 : 0;') === '1';
  if (usesHpos !== HPOS) {
    throw new Error(`WooCommerce uses ${usesHpos ? 'HPOS' : 'legacy'} order storage, but PLANZER_E2E_HPOS=${HPOS ? '1' : '0'}. Run bin/setup.sh again.`);
  }
}
