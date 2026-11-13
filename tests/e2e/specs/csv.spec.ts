/**
 * CSV content S40–S47, docs/testing-concept.md §5 "CSV content".
 * Field numbers follow PAKET Standard V17.
 */
import { expect, test } from '@playwright/test';
import { ANNA, MARCO, createOrder, field, golden, maskCsv, onlyCsv, packageNumber, reset, sftpFiles } from '../lib/env';

test('S40 private customer, shipping = billing → golden/private.csv', async () => {
  const start = await reset();
  const orderId = createOrder({ billing: ANNA, status: 'processing' });
  const files = sftpFiles();
  expect(files).toHaveLength(1);
  expect(maskCsv(files[0].content)).toBe(maskCsv(golden('private', orderId, start + 1)));
});

test('S47 company customer → golden/company.csv, A1_41 = company', async () => {
  const start = await reset();
  const orderId = createOrder({ billing: MARCO, status: 'processing' });
  const files = sftpFiles();
  expect(maskCsv(files[0].content)).toBe(maskCsv(golden('company', orderId, start + 1)));
  expect(field(onlyCsv(), 'A1_41')).toBe('Beispiel GmbH');
});

test('S41 different shipping address → A1_39–A1_47 from shipping, A1_51 and A1_54 empty', async () => {
  await reset();
  createOrder({
    billing: ANNA,
    shipping: { first_name: 'Peter', last_name: 'Muster', company: 'Lager AG', address_1: 'Rue du Lac 8', city: 'Genève', postcode: '1201', country: 'CH' },
    status: 'processing',
  });
  const csv = onlyCsv();
  expect([field(csv, 'A1_39'), field(csv, 'A1_40'), field(csv, 'A1_41')]).toEqual(['Peter', 'Muster', 'Lager AG']);
  expect([field(csv, 'A1_43'), field(csv, 'A1_44'), field(csv, 'A1_45'), field(csv, 'A1_46'), field(csv, 'A1_47')])
    .toEqual(['CH', 'Genève', '1201', 'Rue du Lac', '8']);
  expect(field(csv, 'A1_51')).toBe('');
  expect(field(csv, 'A1_54')).toBe('');
});

for (const [address, street, number] of [
  ['Bahnhofstrasse 12a', 'Bahnhofstrasse', '12a'],
  ['Rue du Lac', 'Rue du Lac', ''],
  ['Via 5 Maggio 3', 'Via 5 Maggio', '3'],
] as const) {
  test(`S42 street parsing "${address}" → A1_46 "${street}", A1_47 "${number}"`, async () => {
    await reset();
    createOrder({ billing: { ...ANNA, address_1: address }, status: 'processing' });
    const csv = onlyCsv();
    expect(field(csv, 'A1_46')).toBe(street);
    expect(field(csv, 'A1_47')).toBe(number);
  });
}

test('S43 notification toggles → A1_22, A1_53, A1_55 (yes = empty, no = B/A/B)', async () => {
  await reset({ options: {
    planzer_notifications_sender_email_notifications: 'no',
    planzer_notifications_receiver_sms_notification: 'no',
    planzer_notifications_receiver_email_notification: 'no',
  } });
  createOrder({ status: 'processing' });
  const csv = onlyCsv();
  expect([field(csv, 'A1_22'), field(csv, 'A1_53'), field(csv, 'A1_55')]).toEqual(['B', 'A', 'B']);

  await reset();
  createOrder({ status: 'processing' });
  const yes = onlyCsv();
  expect([field(yes, 'A1_22'), field(yes, 'A1_53'), field(yes, 'A1_55')]).toEqual(['', '', '']);
});

test('S44 deposit notice 1 / 2 / 3 → O1_1 and A1_82', async () => {
  const expected = { '1': ['Depot', ''], '2': ['', 'Vor der Tür deponieren'], '3': ['', ''] } as const;
  for (const [notice, [o1, a82]] of Object.entries(expected)) {
    await reset({ options: { planzer_notifications_deposit_notice: notice } });
    createOrder({ status: 'processing' });
    const csv = onlyCsv();
    expect([field(csv, 'O1_1'), field(csv, 'A1_82')], `deposit notice ${notice}`).toEqual([o1, a82]);
  }
});

test('S45 chargeable delivery time → A1_73', async () => {
  for (const value of ['', 'BIS10', 'BIS12']) {
    await reset({ options: { planzer_delivery_delivery_time_chargable: value } });
    createOrder({ status: 'processing' });
    expect(field(onlyCsv(), 'A1_73'), value || 'next day').toBe(value);
  }
});

test('S46 package number = 91 + customer no. (6) + branch (3) + sequence (9)', async () => {
  const start = await reset();
  createOrder({ status: 'processing' });
  expect(field(onlyCsv(), 'P1_8')).toBe(packageNumber(start + 1));
  expect(packageNumber(start + 1)).toMatch(/^91123456057\d{9}$/);
});
