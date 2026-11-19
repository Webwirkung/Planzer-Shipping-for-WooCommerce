/**
 * Customer side S60–S61 and settings S70–S72, docs/testing-concept.md §5 "Customer side and settings".
 */
import { expect, test } from '@playwright/test';
import { ANNA, createOrder, mailHtml, mails, orderInfo, reset, setStatus, wp } from '../lib/env';

test('S60 order page for the customer shows "Track your package" with the package number', async ({ browser }) => {
  await reset();
  const orderId = createOrder({ status: 'processing' });
  const tracking = orderInfo(orderId).tracking;
  const url = wp('eval', `echo wc_get_order(${orderId})->get_checkout_order_received_url();`);

  const context = await browser.newContext({ storageState: { cookies: [], origins: [] } });
  const page = await context.newPage();
  await page.goto(url);
  const link = page.locator('a', { hasText: 'Click here' });
  await expect(page.getByText('Track your package:')).toBeVisible();
  await expect(link).toHaveAttribute('href', `https://tracking.app.planzer.ch/delivery/info?packageNumber=${tracking}&system=1`);
  await context.close();
});

test('S61 customer emails sent after the transmission contain the tracking line', async () => {
  await reset();
  const orderId = createOrder({ status: 'processing' });
  const tracking = orderInfo(orderId).tracking;
  setStatus(orderId, 'completed');

  const customer = (await mails()).filter((m) => m.To[0].Address === ANNA.email);
  const completed = customer.find((m) => /on its way|complete/i.test(m.Subject));
  expect(completed, customer.map((m) => m.Subject).join(', ')).toBeDefined();
  expect(await mailHtml(completed!.ID)).toContain(`packageNumber=${tracking}`);

  // The "processing" mail goes out before the plugin reacts to the same status change,
  // so it has no tracking line yet (documents current behaviour).
  const processing = customer.find((m) => /received/i.test(m.Subject));
  expect(await mailHtml(processing!.ID)).not.toContain('packageNumber=');
});

test('S70 every settings section saves and keeps its values; required fields are marked', async ({ page }) => {
  await reset();
  const fields = [
    ['sender', 'planzer_sender_company_name', 'UI Test AG'],
    ['ftp', 'planzer_ftp_live_account_id', '999'],
    ['delivery', 'planzer_delivery_note_contact_name', 'UI Kontakt'],
    ['notifications', 'planzer_notifications_deposit_notice_information', 'Im Milchkasten'],
  ] as const;
  for (const [section, name, value] of fields) {
    await page.goto(`/wp-admin/admin.php?page=wc-settings&tab=planzer&section=${section}`);
    await page.locator(`[name="${name}"]`).fill(value);
    await page.locator('button[name="save"]').click();
    await expect(page.locator('#message.updated, .notice-success').first()).toBeVisible();
    await expect(page.locator(`[name="${name}"]`)).toHaveValue(value);
    expect(wp('option', 'get', name)).toBe(value);
  }
  await page.goto('/wp-admin/admin.php?page=wc-settings&tab=planzer&section=sender');
  await expect(page.locator('[name="planzer_sender_company_name"]')).toHaveAttribute('required', /.*/);
  await reset();
});

test('S71 Other section: product search by AJAX, no full product list in the page (eb2fff9)', async ({ page }) => {
  await reset();
  await page.goto('/wp-admin/admin.php?page=wc-settings&tab=planzer&section=other');
  const select = page.locator('select[name="planzer_other_excluded_products[]"]');
  await expect(select).toHaveClass(/wc-product-search/);
  await expect(select.locator('option')).toHaveCount(0);

  await select.locator('xpath=following-sibling::span[contains(@class,"select2")]').click();
  await page.keyboard.type('Bean');
  await expect(page.locator('.select2-results__option', { hasText: 'Beanie' })).toBeVisible();
});

test('S72 WooCommerce inactive: admin notice and the plugin deactivates itself', async ({ page }) => {
  wp('plugin', 'deactivate', 'woocommerce');
  try {
    await page.goto('/wp-admin/plugins.php');
    await expect(page.locator('.notice-error', { hasText: 'PLANZER' })).toBeVisible();
    expect(wp('plugin', 'list', '--status=active', '--field=name')).not.toContain('wc-planzer-shipping');
  } finally {
    wp('plugin', 'activate', 'woocommerce');
    wp('plugin', 'activate', 'wc-planzer-shipping');
  }
});
