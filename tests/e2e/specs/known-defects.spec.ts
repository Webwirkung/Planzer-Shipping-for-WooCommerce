/**
 * Known defects, docs/testing-concept.md §7. Each test describes the correct behaviour and is
 * marked test.fail(): it runs and must fail. When a defect is fixed the test turns red –
 * then remove the marker and the test guards the fix.
 */
import { expect, Page, test } from '@playwright/test';
import {
  createOrder, field, ids, onlyCsv, orderCheckbox, orderEditUrl, orderInfo, ordersListUrl, parseCsv, planzerMails, reset, setStatus, sftpFiles,
} from '../lib/env';

async function bulkTransmit(page: Page, orderIds: number[]): Promise<void> {
  await page.goto(ordersListUrl());
  for (const id of orderIds) await page.locator(orderCheckbox(id)).check();
  await page.locator('#bulk-action-selector-top').selectOption('wc-planzer-transmit');
  await page.locator('#doaction').click();
  // No URL check here: in test mode (D2) the redirect has no wc-planzer-transmit parameter.
  await page.waitForLoadState('load');
}

test('D1 order action respects test mode: no CSV upload, TEST_ tracking', async ({ page }) => {
  test.fail(true, 'D1');
  await reset({ options: { planzer_ftp_test_mode: 'yes' } });
  const orderId = createOrder({ status: 'on-hold' });
  await page.goto(orderEditUrl(orderId));
  await page.locator('select[name="wc_order_action"]').selectOption('planzer_generate_delivery_note');
  await page.locator('button.save_order').click();
  await expect(page.locator('#message, .notice-success').first()).toBeVisible();

  expect(sftpFiles()).toHaveLength(0);
  expect(orderInfo(orderId).tracking).toMatch(/^TEST_/);
});

test('D2 bulk in test mode handles every selected order', async ({ page }) => {
  test.fail(true, 'D2');
  await reset({ options: { planzer_ftp_test_mode: 'yes', planzer_sender_enable_manual_orders: 'yes' } });
  const orders = [createOrder({ status: 'processing' }), createOrder({ status: 'processing' })];
  for (const id of orders) setStatus(id, 'planzer-transmit');
  await reset({ options: { planzer_ftp_test_mode: 'yes', planzer_sender_enable_manual_orders: 'yes' } });

  await bulkTransmit(page, orders);

  expect(await planzerMails()).toHaveLength(2);
  await expect(page.locator('#message')).toBeVisible();
});

test('D3 A1_40 without shipping last name falls back to the billing last name', async () => {
  test.fail(true, 'D3');
  await reset();
  createOrder({ shipping: { first_name: 'Anna', last_name: '', address_1: 'Bahnhofstrasse 12a', city: 'Zürich', postcode: '8001', country: 'CH' }, status: 'processing' });
  expect(field(onlyCsv(), 'A1_40')).toBe('Beispiel');
});

test('D5 bulk increases the QR counter once per order', async ({ page }) => {
  test.fail(true, 'D5');
  const start = await reset({ options: { planzer_sender_enable_manual_orders: 'yes' } });
  const orders = [createOrder({ status: 'processing' }), createOrder({ status: 'processing' })];
  await bulkTransmit(page, orders);
  expect(orderInfo(orders[0]).sequence).toBe(start + 2);
});

test('D7 a ";" in the address does not shift the CSV columns', async () => {
  test.fail(true, 'D7');
  await reset();
  createOrder({ billing: { first_name: 'Anna', last_name: 'Beispiel', address_1: 'Bahnhofstrasse 12a; Hinterhaus', city: 'Zürich', postcode: '8001', country: 'CH', email: 'anna@example.test' }, status: 'processing' });
  const csv = parseCsv(sftpFiles()[0].content);
  expect(csv.A1).toHaveLength(87);
  expect(field(csv, 'A1_67')).toMatch(/^\d\d\.\d\d\.\d{4}$/);
});

test('D8 bulk notice does not report excluded orders as transmitted', async ({ page }) => {
  test.fail(true, 'D8');
  await reset({ options: { planzer_sender_enable_manual_orders: 'yes', planzer_other_excluded_products: [ids().products['E2E-MUG']] } });
  const excluded = createOrder({ items: [['E2E-MUG', 1]], status: 'processing' });
  await bulkTransmit(page, [excluded]);
  await expect(page.locator('#message.notice-success')).not.toContainText(String(excluded));
});

test('D10 deposit notice 2 does not fill A1_76 (deposit notice for the pick-up at the sender)', async () => {
  test.fail(true, 'D10');
  await reset();
  createOrder({ status: 'processing' });
  expect(field(onlyCsv(), 'A1_76')).toBe('');
});
