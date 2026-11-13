/**
 * Pickup/delivery dates S50–S56, docs/testing-concept.md §5 "Dates".
 * The clock is set through the planzer/csv/now filter (tests/e2e/mu-plugins).
 */
import { expect, test } from '@playwright/test';
import { createOrder, field, onlyCsv, reset } from '../lib/env';

// 2026-11-11 is a Wednesday.
const cases = [
  { id: 'S50', now: '2026-11-11 14:59', saturday: 'no', pickup: '11.11.2026', delivery: '12.11.2026', label: 'Wed 14:59' },
  { id: 'S51', now: '2026-11-11 15:00', saturday: 'no', pickup: '12.11.2026', delivery: '13.11.2026', label: 'Wed 15:00' },
  { id: 'S52', now: '2026-11-13 14:00', saturday: 'no', pickup: '13.11.2026', delivery: '16.11.2026', label: 'Fri 14:00' },
  { id: 'S53', now: '2026-11-13 14:00', saturday: 'yes', pickup: '13.11.2026', delivery: '14.11.2026', label: 'Fri 14:00, Saturday delivery' },
  { id: 'S54', now: '2026-11-13 15:00', saturday: 'no', pickup: '16.11.2026', delivery: '17.11.2026', label: 'Fri 15:00' },
  { id: 'S55', now: '2026-11-14 10:00', saturday: 'no', pickup: '16.11.2026', delivery: '17.11.2026', label: 'Sat 10:00' },
  { id: 'S56', now: '2026-11-11 13:00', saturday: 'no', pickup: '12.11.2026', delivery: '13.11.2026', label: 'Wed 13:00, cutoff 12:00', cutoff: '12:00' },
];

for (const c of cases) {
  test(`${c.id} ${c.label} → pickup ${c.pickup}, delivery ${c.delivery}`, async () => {
    await reset({
      now: c.now,
      options: { planzer_delivery_saturday_delivery: c.saturday, planzer_sender_pickup_time_today: c.cutoff ?? '15:00' },
    });
    createOrder({ status: 'processing' });
    const csv = onlyCsv();
    expect(field(csv, 'A1_34')).toBe(c.pickup);
    expect(field(csv, 'A1_67')).toBe(c.delivery);
  });
}
