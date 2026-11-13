/**
 * Transmission S01–S10, docs/testing-concept.md §5 "Transmission".
 */
import { expect, test } from '@playwright/test';
import {
  ANNA, createOrder, golden, maskCsv, orderEditUrl, orderInfo, packageNumber, planzerMails, reset, setStatus, sftpFiles, wp,
} from '../lib/env';

/** "sent" from the concept: CSV = golden, one mail with PDF, tracking meta, success note, counter +1. */
async function expectSent(orderId: number, sequence: number, goldenName = 'private'): Promise<void> {
  const files = sftpFiles();
  expect(files).toHaveLength(1);
  expect(files[0].name).toMatch(new RegExp(`^PAKET_${String(orderId).padStart(20, '0')}_\\d+_\\d+_WP\\.csv$`));
  expect(maskCsv(files[0].content)).toBe(maskCsv(golden(goldenName, orderId, sequence)));

  const mails = await planzerMails();
  expect(mails).toHaveLength(1);
  expect(mails[0].Subject).toBe(`Planzer label note for order ${orderId}_${sequence}`);
  expect(mails[0].To[0].Address).toBe('notes@example.test');
  expect(mails[0].Attachments).toBe(1);

  const info = orderInfo(orderId);
  expect(info.sequence).toBe(sequence);
  expect(info.tracking).toBe(packageNumber(sequence));
  const note = info.notes.find((n) => n.startsWith('Planzer: delivery/label note and CSV generated.'));
  expect(note, info.notes.join('\n---\n')).toBeDefined();
  expect(note).toContain(`Reference number: ${orderId}_${sequence}`);
  expect(note).toContain(packageNumber(sequence));
}

test('S01 classic checkout, order goes to processing → sent', async ({ browser }) => {
  const start = await reset();
  // A guest, not the logged-in admin.
  const context = await browser.newContext({ storageState: { cookies: [], origins: [] } });
  const page = await context.newPage();
  const productId = wp('wc', 'product', 'list', '--sku=E2E-BEANIE', '--field=id', '--user=admin');

  await page.goto(`/?add-to-cart=${productId}`);
  await page.goto('/classic-checkout/');
  await page.fill('#billing_first_name', ANNA.first_name);
  await page.fill('#billing_last_name', ANNA.last_name);
  await page.fill('#billing_address_1', ANNA.address_1);
  await page.fill('#billing_postcode', ANNA.postcode);
  await page.fill('#billing_city', ANNA.city);
  await page.fill('#billing_phone', ANNA.phone!);
  await page.fill('#billing_email', ANNA.email!);
  await page.locator('#ship-to-different-address-checkbox').uncheck();
  await page.locator('#payment_method_cod').check({ force: true });
  await page.locator('#place_order').click();
  await page.waitForURL(/order-received\/(\d+)/);
  const orderId = Number(/order-received\/(\d+)/.exec(page.url())![1]);
  await context.close();

  expect(orderInfo(orderId).status).toBe('processing');
  await expectSent(orderId, start + 1);
});

test('S02 processing → on-hold → processing sends twice (current behaviour, D4)', async () => {
  const start = await reset();
  const orderId = createOrder({ status: 'processing' });
  setStatus(orderId, 'on-hold');
  setStatus(orderId, 'processing');

  expect(sftpFiles()).toHaveLength(2);
  expect(await planzerMails()).toHaveLength(2);
  expect(orderInfo(orderId).sequence).toBe(start + 2);
});

test('S03 test mode: no CSV, PDF mail marked [TEST MODE], TEST_ tracking', async () => {
  const start = await reset({ options: { planzer_ftp_test_mode: 'yes' } });
  const orderId = createOrder({ status: 'processing' });

  expect(sftpFiles()).toHaveLength(0);
  const mails = await planzerMails();
  expect(mails).toHaveLength(1);
  expect(mails[0].Subject).toContain('[TEST MODE]');

  const info = orderInfo(orderId);
  // Test mode uses sequence 0 in the package number; the counter still goes up.
  expect(info.tracking).toBe(`TEST_${packageNumber(0)}`);
  expect(info.sequence).toBe(start + 1);
  expect(info.notes.join('\n')).toContain('Test mode enabled - data not sent to Planzer. Demo delivery note generated and sent.');
});

test('S04 manual mode: processing does nothing', async () => {
  const start = await reset({ options: { planzer_sender_enable_manual_orders: 'yes' } });
  const orderId = createOrder({ status: 'processing' });

  expect(sftpFiles()).toHaveLength(0);
  expect(await planzerMails()).toHaveLength(0);
  const info = orderInfo(orderId);
  expect(info.sequence).toBe(start);
  expect(info.notes.filter((n) => n.includes('Planzer'))).toEqual([]);
});

test('S05 manual mode: status "Transmit to Planzer" on the order screen → sent', async ({ page }) => {
  const start = await reset({ options: { planzer_sender_enable_manual_orders: 'yes' } });
  const orderId = createOrder({ status: 'processing' });

  await page.goto(orderEditUrl(orderId));
  await page.locator('#order_status').selectOption('wc-planzer-transmit', { force: true });
  await page.locator('button.save_order').click();
  await expect(page.locator('#message, .notice-success').first()).toBeVisible();

  expect(orderInfo(orderId).status).toBe('planzer-transmit');
  await expectSent(orderId, start + 1);
});

test('S07 order action "Generate Planzer note and create package" → sent', async ({ page }) => {
  const start = await reset();
  const orderId = createOrder({ status: 'on-hold' });

  await page.goto(orderEditUrl(orderId));
  await page.locator('select[name="wc_order_action"]').selectOption('planzer_generate_delivery_note');
  await page.locator('button.save_order').click();
  await expect(page.locator('#message, .notice-success').first()).toBeVisible();

  await expectSent(orderId, start + 1);
});

test('S08 SFTP down: red error note, log entry; tracking and mail already done (D6)', async () => {
  await reset({ options: { planzer_ftp_live_server_address: 'sftp-unreachable.invalid' } });
  const orderId = createOrder({ status: 'processing' });

  const info = orderInfo(orderId);
  expect(info.notes.join('\n')).toContain('There was an error while sending data to Planzer');
  expect(info.tracking).not.toBe('');
  expect(await planzerMails()).toHaveLength(1);
  const log = wp('eval', 'echo implode("\\n", array_map("file_get_contents", glob(WP_CONTENT_DIR . "/uploads/wc-logs/wc-planzer-shipping-*.log")));');
  expect(log).toContain('ERROR connecting to Planzer SFTP server failed');
});

test('S10 two recipients in planzer_delivery_note_email → two mails', async () => {
  await reset({ options: { planzer_delivery_note_email: 'notes@example.test,office@example.test' } });
  createOrder({ status: 'processing' });

  const to = (await planzerMails()).map((m) => m.To[0].Address).sort();
  expect(to).toEqual(['notes@example.test', 'office@example.test']);
});
