/**
 * Exclusions S30–S37, docs/testing-concept.md §4 P5 and §5 "Exclusions".
 */
import { expect, test } from '@playwright/test';
import { createOrder, ids, orderInfo, planzerMails, reset, sftpFiles } from '../lib/env';

const REASON = {
  products: 'Products in the order are excluded from Planzer delivery',
  method: 'The order shipping method is excluded from Planzer delivery',
  classes: 'The order contains products with excluded shipping classes',
};

async function expectNotSent(orderId: number, reason: string, start: number): Promise<void> {
  expect(sftpFiles()).toHaveLength(0);
  expect(await planzerMails()).toHaveLength(0);
  const info = orderInfo(orderId);
  expect(info.notes.join('\n')).toContain(`Planzer: ${reason}`);
  expect(info.tracking).toBe('');
  expect(info.sequence, 'counter goes up even when excluded').toBe(start + 1);
}

test('S30 all products excluded → not sent', async () => {
  const start = await reset({ options: { planzer_other_excluded_products: [ids().products['E2E-MUG']] } });
  const orderId = createOrder({ items: [['E2E-MUG', 2]], status: 'processing' });
  await expectNotSent(orderId, REASON.products, start);
});

test('S31 one of two products excluded → sent', async () => {
  await reset({ options: { planzer_other_excluded_products: [ids().products['E2E-MUG']] } });
  createOrder({ items: [['E2E-MUG', 1], ['E2E-BEANIE', 1]], status: 'processing' });
  expect(sftpFiles()).toHaveLength(1);
});

test('S32 excluded shipping method → not sent', async () => {
  const start = await reset({ options: { planzer_other_excluded_shipping: ['Free shipping'] } });
  const orderId = createOrder({ shipping_method: 'free_shipping', status: 'processing' });
  await expectNotSent(orderId, REASON.method, start);
});

test('S33 flat rate per class, all classes excluded → not sent', async () => {
  const { classes } = ids();
  const start = await reset({ options: { planzer_other_excluded_shipping_classes: [String(classes.small), String(classes.bulky)] } });
  const orderId = createOrder({ items: [['E2E-BEANIE', 1], ['E2E-HOODIE', 1]], status: 'processing' });
  await expectNotSent(orderId, REASON.classes, start);
});

test('S34 flat rate per class, one class not excluded → sent', async () => {
  await reset({ options: { planzer_other_excluded_shipping_classes: [String(ids().classes.small)] } });
  createOrder({ items: [['E2E-BEANIE', 1], ['E2E-HOODIE', 1]], status: 'processing' });
  expect(sftpFiles()).toHaveLength(1);
});

test('S35 flat rate per order, most expensive class excluded → not sent', async () => {
  // Bulky costs 15, Small 5: Bulky decides.
  const start = await reset({ flat_rate_type: 'order', options: { planzer_other_excluded_shipping_classes: [String(ids().classes.bulky)] } });
  const orderId = createOrder({ items: [['E2E-BEANIE', 1], ['E2E-HOODIE', 1]], status: 'processing' });
  await expectNotSent(orderId, REASON.classes, start);

  // Cheaper class excluded: sent.
  await reset({ flat_rate_type: 'order', options: { planzer_other_excluded_shipping_classes: [String(ids().classes.small)] } });
  createOrder({ items: [['E2E-BEANIE', 1], ['E2E-HOODIE', 1]], status: 'processing' });
  expect(sftpFiles()).toHaveLength(1);
});

test('S36 "none" saved together with real values: exclusion still applies (cb791c7, c503050)', async () => {
  const { products, classes } = ids();
  const cases = [
    { options: { planzer_other_excluded_products: ['none', products['E2E-MUG']] }, order: { items: [['E2E-MUG', 1]] as [string, number][] }, reason: REASON.products },
    { options: { planzer_other_excluded_shipping: ['none', 'Free shipping'] }, order: { shipping_method: 'free_shipping' as const }, reason: REASON.method },
    { options: { planzer_other_excluded_shipping_classes: ['none', String(classes.small)] }, order: { items: [['E2E-BEANIE', 1]] as [string, number][] }, reason: REASON.classes },
  ];
  for (const c of cases) {
    const start = await reset({ options: c.options });
    const orderId = createOrder({ ...c.order, status: 'processing' });
    await expectNotSent(orderId, c.reason, start);
  }
});

test('S37 only "none" saved → nothing excluded', async () => {
  await reset({ options: {
    planzer_other_excluded_products: ['none'],
    planzer_other_excluded_shipping: ['none'],
    planzer_other_excluded_shipping_classes: ['none'],
  } });
  createOrder({ items: [['E2E-MUG', 1]], shipping_method: 'free_shipping', status: 'processing' });
  expect(sftpFiles()).toHaveLength(1);
});
