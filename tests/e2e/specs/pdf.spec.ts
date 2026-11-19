/**
 * PDF S80–S87, docs/testing-concept.md §5 "PDF".
 * Four levels: format (pdfinfo), content (pdftotext), QR content (zbarimg), QR geometry.
 */
import { expect, test } from '@playwright/test';
import {
  MARCO, attachment, createOrder, expectedQr, field, ids, inspectPdf, onlyCsv, packageNumber, php, planzerMails, reset, setStatus, wp,
} from '../lib/env';

async function onlyPdf() {
  const mails = await planzerMails();
  expect(mails).toHaveLength(1);
  return inspectPdf(await attachment(mails[0].ID));
}

const CONTACT = { planzer_delivery_note_contact_name: 'Erika Muster' };

test('S80 label note: A6 landscape, package number, receiver, contact, sequence; QR = P1_8', async () => {
  const start = await reset({ options: CONTACT });
  createOrder({ status: 'processing' });
  const pdf = await onlyPdf();
  const seq = start + 1;

  expect(pdf.pages).toBe(1);
  expect([pdf.widthMm, pdf.heightMm]).toEqual([148, 105]);
  expect(pdf.text).toContain(packageNumber(seq));
  expect(pdf.text).toContain('Anna Beispiel');
  expect(pdf.text).toContain('Erika Muster');
  expect(pdf.text).toMatch(new RegExp(`\\b${seq}\\b`));
  expect(pdf.qr).toEqual([expectedQr(seq)]);
  expect(pdf.qr[0]).toHaveLength(70);
  expect(pdf.qr[0].slice(0, 20)).toBe(field(onlyCsv(), 'P1_8'));
});

test('S81 delivery note: A4 portrait with sender, receiver, LS number, items; QR = P1_8', async () => {
  const start = await reset({ options: { ...CONTACT, planzer_delivery_generate_note: 'delivery_note' } });
  const orderId = createOrder({ billing: MARCO, items: [['E2E-BEANIE', 2], ['E2E-HOODIE', 1]], status: 'processing' });
  wp('eval', `$o = wc_get_order(${orderId}); $o->set_customer_note('Bitte beim Nachbarn abgeben'); $o->save();`);
  // Customer note is read when the PDF is built, so send again after setting it.
  await reset({ options: { ...CONTACT, planzer_delivery_generate_note: 'delivery_note' } });
  setStatus(orderId, 'on-hold');
  setStatus(orderId, 'processing');
  const seq = start + 2;
  const pdf = await onlyPdf();

  expect([pdf.widthMm, pdf.heightMm]).toEqual([210, 297]);
  expect(pdf.text).toContain('Muster Versand AG Lager Nord');
  for (const s of ['Marco Rossi', 'Beispiel GmbH', 'Via Cantonale 5', '6900 Lugano', 'Switzerland']) {
    expect(pdf.text).toContain(s);
  }
  expect(pdf.text).toMatch(new RegExp(`Delivery note LS-${seq} / Order number ${orderId}`));
  expect(pdf.text).toContain('Erika Muster');
  expect(pdf.text).toMatch(/Beanie\s[\s\S]*Product Code: E2E-BEANIE/);
  expect(pdf.text).toMatch(/Hoodie\s[\s\S]*Product Code: E2E-HOODIE/);
  expect(pdf.text).toContain('Bitte beim Nachbarn abgeben');
  expect(pdf.qr).toEqual([expectedQr(seq)]);
});

test('S82 delivery note: partial refund reduces quantity; full refund and excluded lines are missing', async () => {
  await reset({ options: { planzer_delivery_generate_note: 'delivery_note', planzer_other_excluded_products: [ids().products['E2E-MUG']] } });
  const orderId = createOrder({ items: [['E2E-BEANIE', 3], ['E2E-HOODIE', 1], ['E2E-MUG', 1]], status: 'on-hold' });
  php('refund.php', String(orderId), JSON.stringify({ 'E2E-BEANIE': 1, 'E2E-HOODIE': 1 }));
  setStatus(orderId, 'processing');
  const pdf = await onlyPdf();

  expect(pdf.text).toContain('E2E-BEANIE');
  expect(pdf.text).not.toContain('E2E-HOODIE');
  expect(pdf.text).not.toContain('E2E-MUG');
  // Beanie row (item 1): ordered 2 (3 - 1 refunded), open 0, delivered 2.
  expect(pdf.text).toMatch(/^1\s+2\s+0\s+2\s*$/m);
});

for (const [locale, heading, salutation] of [
  ['de_DE', 'Lieferschein LS', 'Meine Damen und Herren'],
  ['fr_FR', 'Bon de livraison LS', 'Mesdames, Messieurs'],
  ['en_US', 'Delivery note LS', 'Ladies and gentlemen'],
  // The plugin ships no Italian translation (resources/lang has de and fr only): English.
  ['it_IT', 'Delivery note LS', 'Ladies and gentlemen'],
] as const) {
  test(`S83 delivery note in ${locale}: "${heading}"`, async () => {
    // The PDF follows the WordPress locale; planzer_sender_language only goes into the CSV.
    await reset({ options: { planzer_delivery_generate_note: 'delivery_note', WPLANG: locale === 'en_US' ? '' : locale } });
    try {
      createOrder({ status: 'processing' });
      const pdf = await onlyPdf();
      expect(pdf.text).toContain(heading);
      expect(pdf.text).toContain(salutation);
    } finally {
      wp('option', 'update', 'WPLANG', '');
    }
  });
}

test('S84 salutation, signature and footer placeholders are all replaced', async () => {
  await reset({ options: {
    ...CONTACT,
    planzer_delivery_generate_note: 'delivery_note',
    planzer_delivery_note_salutation: 'Hallo {first_name} {last_name}',
    planzer_delivery_note_signature: 'Gruss {contact_name}, {company}',
    planzer_delivery_note_footer: '{company} | {address} | {email} | {website}',
  } });
  createOrder({ status: 'processing' });
  const pdf = await onlyPdf();

  expect(pdf.text).toContain('Hallo Anna Beispiel');
  expect(pdf.text).toContain('Gruss Erika Muster, Muster Versand AG');
  expect(pdf.text).toContain('versand@example.test');
  expect(pdf.text).not.toMatch(/\{[a-z_]+\}/);
});

test('S85 test mode: same label, QR uses sequence 0', async () => {
  await reset({ options: { planzer_ftp_test_mode: 'yes' } });
  createOrder({ status: 'processing' });
  const pdf = await onlyPdf();
  expect([pdf.widthMm, pdf.heightMm]).toEqual([148, 105]);
  expect(pdf.qr).toEqual([expectedQr(0)]);
});

for (const note of ['label_note', 'delivery_note'] as const) {
  test(`S86 QR geometry on the ${note}: module ≥ 0.5 mm, quiet zone ≥ 4 modules`, async () => {
    // D12: on the delivery note the "Package number" line sits 3.6 modules above the QR code.
    test.fail(note === 'delivery_note', 'D12 delivery note QR quiet zone below 4 modules');
    await reset({ options: { planzer_delivery_generate_note: note } });
    createOrder({ status: 'processing' });
    const geo = (await onlyPdf()).qrGeometry!;
    test.info().annotations.push({ type: 'QR edge (PAKET V17: 48 mm)', description: `${geo.edge_mm} mm` });
    expect(geo.module_mm).toBeGreaterThanOrEqual(0.5);
    expect(geo.quiet_modules).toBeGreaterThanOrEqual(4);
  });
}

test('S87 api.qrserver.com unreachable: documents what the merchant gets', async () => {
  await reset({ options: { planzer_e2e_block_qrserver: 'yes' } });
  try {
    createOrder({ status: 'processing' });
    const mails = await planzerMails();
    const pdf = mails.length ? inspectPdf(await attachment(mails[0].ID)) : undefined;
    test.info().annotations.push({ type: 'result', description: pdf ? `PDF sent, QR codes found: ${pdf.qr.length}` : 'no PDF mail' });
    // Current behaviour: the label still goes out, without a readable QR code.
    expect(pdf?.qr ?? []).toEqual([]);
  } finally {
    wp('option', 'delete', 'planzer_e2e_block_qrserver');
  }
});
