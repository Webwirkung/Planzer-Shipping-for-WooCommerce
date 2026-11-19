/**
 * Admin UI S06, S90–S96, docs/testing-concept.md §5 "Admin UI".
 * Guards against buttons and options disappearing. Runs on the HPOS and the legacy screens (matrix).
 */
import { expect, test } from '@playwright/test';
import { createOrder, orderEditUrl, orderInfo, ordersListUrl, reset, setStatus, sftpFiles } from '../lib/env';

const MANUAL = { planzer_sender_enable_manual_orders: 'yes' };

test('S90 manual mode: status select has "Transmit to Planzer"; saving it transmits', async ({ page }) => {
  await reset({ options: MANUAL });
  const orderId = createOrder({ status: 'processing' });
  await page.goto(orderEditUrl(orderId));
  const option = page.locator('#order_status option[value="wc-planzer-transmit"]');
  await expect(option).toHaveCount(1);
  await expect(option).toHaveText('Transmit to Planzer');

  await page.locator('#order_status').selectOption('wc-planzer-transmit', { force: true });
  await page.locator('button.save_order').click();
  await expect(page.locator('#message, .notice-success').first()).toBeVisible();
  expect(orderInfo(orderId).status).toBe('planzer-transmit');
  expect(sftpFiles()).toHaveLength(1);
});

test('S91 manual mode: bulk selects have "Transmit to Planzer"; status view is listed', async ({ page }) => {
  await reset({ options: MANUAL });
  const orderId = createOrder({ status: 'processing' });
  setStatus(orderId, 'planzer-transmit');
  await page.goto(ordersListUrl());
  for (const select of ['#bulk-action-selector-top', '#bulk-action-selector-bottom']) {
    await expect(page.locator(`${select} option[value="wc-planzer-transmit"]`)).toHaveText('Transmit to Planzer');
  }
  await expect(page.locator('ul.subsubsub')).toContainText(/Transmit to Planzer\s*\(\d+\)/);
});

test('S06/S92 automatic mode: no "Transmit to Planzer" in status select or bulk selects', async ({ page }) => {
  await reset();
  const orderId = createOrder({ status: 'on-hold' });
  await page.goto(orderEditUrl(orderId));
  await expect(page.locator('#order_status option[value="wc-planzer-transmit"]')).toHaveCount(0);
  await page.goto(ordersListUrl());
  await expect(page.locator('#bulk-action-selector-top option[value="wc-planzer-transmit"]')).toHaveCount(0);
  await expect(page.locator('#bulk-action-selector-bottom option[value="wc-planzer-transmit"]')).toHaveCount(0);
});

for (const manual of ['no', 'yes']) {
  test(`S93 order action "Generate Planzer note and create package" is listed (manual mode ${manual})`, async ({ page }) => {
    await reset({ options: { planzer_sender_enable_manual_orders: manual } });
    const orderId = createOrder({ status: 'on-hold' });
    await page.goto(orderEditUrl(orderId));
    await expect(page.locator('select[name="wc_order_action"] option[value="planzer_generate_delivery_note"]'))
      .toHaveText('Generate Planzer note and create package');
  });
}

test('S94 WooCommerce › Settings › Planzer: five sections with fields and a save button', async ({ page }) => {
  await reset();
  await page.goto('/wp-admin/admin.php?page=wc-settings&tab=planzer');
  const sections = page.locator('ul.subsubsub a');
  for (const name of ['Sender', 'FTP', 'Notifications', 'Delivery', 'Other']) {
    await expect(sections.filter({ hasText: name })).toHaveCount(1);
  }
  for (const section of ['sender', 'ftp', 'notifications', 'delivery', 'other']) {
    await page.goto(`/wp-admin/admin.php?page=wc-settings&tab=planzer&section=${section}`);
    await expect(page.locator('table.form-table [name^="planzer_"]').first(), section).toBeAttached();
    await expect(page.locator('button[name="save"], .woocommerce-save-button')).toHaveCount(1);
  }
});

test('S95 plugins screen: "Settings" link opens Planzer › Sender', async ({ page }) => {
  await page.goto('/wp-admin/plugins.php');
  await page.locator('tr[data-plugin="wc-planzer-shipping/wc-planzer-shipping.php"] a', { hasText: 'Settings' }).click();
  await expect(page).toHaveURL(/page=wc-settings&tab=planzer&section=sender/);
});

test('S96 plugin assets load without 404 and without console errors', async ({ page }) => {
  await reset();
  const orderId = createOrder({ status: 'on-hold' });
  const failed: string[] = [];
  const errors: string[] = [];
  page.on('response', (r) => { if (r.url().includes('/wc-planzer-shipping/') && r.status() >= 400) failed.push(`${r.status()} ${r.url()}`); });
  page.on('pageerror', (e) => errors.push(e.message));

  for (const url of ['/wp-admin/admin.php?page=wc-settings&tab=planzer&section=notifications', ordersListUrl(), orderEditUrl(orderId)]) {
    await page.goto(url);
    await page.waitForLoadState('networkidle');
  }
  expect(errors).toEqual([]);
  // vendor.js is enqueued in src/Core/Config.php but never built (also missing in the 1.0.36 release).
  expect(failed.filter((f) => !f.endsWith('/dist/scripts/vendor.js') && !f.includes('/dist/scripts/vendor.js?'))).toEqual([]);
});

test('D11 vendor.js is enqueued but not part of the build: 404 on every admin page', async ({ page }) => {
  test.fail(true, 'D11');
  const res = await page.request.get('/wp-content/plugins/wc-planzer-shipping/dist/scripts/vendor.js');
  expect(res.status()).toBe(200);
});
