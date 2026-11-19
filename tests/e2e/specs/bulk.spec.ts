/**
 * Bulk action "Transmit to Planzer" S20–S23, docs/testing-concept.md §4 P3 and §5 "Bulk".
 * S24 (HPOS vs legacy screen) is the HPOS/legacy matrix: the whole file runs in both.
 */
import { expect, Page, test } from '@playwright/test';
import { createOrder, ids, orderCheckbox, orderInfo, ordersListUrl, reset, setStatus, sftpFiles } from '../lib/env';

const MANUAL = { planzer_sender_enable_manual_orders: 'yes' };

async function bulkTransmit(page: Page, orderIds: number[]): Promise<void> {
  await page.goto(ordersListUrl());
  for (const id of orderIds) {
    await page.locator(orderCheckbox(id)).check();
  }
  await page.locator('#bulk-action-selector-top').selectOption('wc-planzer-transmit');
  await page.locator('#doaction').click();
  await page.waitForURL(/wc-planzer-transmit=1/);
}

test('S20 three orders in processing → all in "Transmit to Planzer", 3 CSVs, success notice', async ({ page }) => {
  await reset({ options: MANUAL });
  const orders = [createOrder({ status: 'processing' }), createOrder({ status: 'processing' }), createOrder({ status: 'processing' })];

  await bulkTransmit(page, orders);

  const notice = page.locator('#message.notice-success');
  await expect(notice).toContainText('have proceeded to the planzer.');
  // Order of the numbers follows the rows on the list screen, which differs between HPOS and legacy.
  const listed = /number ([\d,]+) have/.exec(await notice.innerText())![1].split(',').map(Number).sort();
  expect(listed).toEqual([...orders].sort());
  expect(sftpFiles()).toHaveLength(3);
  for (const id of orders) {
    expect(orderInfo(id).status).toBe('planzer-transmit');
  }
});

test('S21 seven orders selected → first five handled, limit warning', async ({ page }) => {
  await reset({ options: MANUAL });
  const orders = Array.from({ length: 7 }, () => createOrder({ status: 'processing' }));

  await bulkTransmit(page, orders);

  await expect(page.locator('#message.notice-warning')).toContainText('The maximum limit of orders to sent to the planzer at one time is 5');
  expect(sftpFiles()).toHaveLength(5);
  const transmitted = orders.filter((id) => orderInfo(id).status === 'planzer-transmit');
  expect(transmitted).toHaveLength(5);
});

test('S22 one of three excluded → keeps status and gets a note, others sent', async ({ page }) => {
  await reset({ options: { ...MANUAL, planzer_other_excluded_products: [ids().products['E2E-MUG']] } });
  const excluded = createOrder({ items: [['E2E-MUG', 1]], status: 'processing' });
  const others = [createOrder({ status: 'processing' }), createOrder({ status: 'processing' })];

  await bulkTransmit(page, [excluded, ...others]);

  expect(sftpFiles()).toHaveLength(2);
  const info = orderInfo(excluded);
  expect(info.status).toBe('processing');
  expect(info.notes.join('\n')).toContain('Products in the order are excluded from Planzer delivery');
});

test('S23 orders already in "Transmit to Planzer" → sent again inline', async ({ page }) => {
  await reset({ options: MANUAL });
  const orders = [createOrder({ status: 'processing' }), createOrder({ status: 'processing' })];
  for (const id of orders) setStatus(id, 'planzer-transmit');
  expect(sftpFiles()).toHaveLength(2);

  await bulkTransmit(page, orders);

  expect(sftpFiles()).toHaveLength(4);
});
