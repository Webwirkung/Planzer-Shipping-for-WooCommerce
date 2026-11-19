/**
 * Helpers that talk to the test environment (docker-compose.yml): WP-CLI in the
 * wordpress container, the SFTP stand-in, Mailpit and the PDF tools.
 * See docs/testing-concept.md §3.
 */
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import path from 'node:path';

export const E2E_DIR = path.resolve(__dirname, '..');
export const HPOS = process.env.PLANZER_E2E_HPOS !== '0';
export const BASE_URL = `http://localhost:${process.env.PLANZER_E2E_PORT ?? '8190'}`;
export const MAILPIT_URL = `http://localhost:${process.env.PLANZER_E2E_MAILPIT_PORT ?? '8195'}`;

function compose(args: string[], input?: string | Buffer): string {
  return execFileSync('docker', ['compose', '-f', path.join(E2E_DIR, 'docker-compose.yml'), ...args], {
    input,
    stdio: ['pipe', 'pipe', 'pipe'],
    maxBuffer: 64 * 1024 * 1024,
  }).toString();
}

/** Runs WP-CLI as www-data and returns stdout. PHP notices go to stderr and are ignored. */
export function wp(...args: string[]): string {
  return compose(['exec', '-T', '-u', 'www-data', 'wordpress', 'wp', ...args]).trim();
}

/** Runs one of fixtures/php/*.php with WP-CLI. */
export function php(script: string, ...args: string[]): string {
  return wp('eval-file', `/e2e/fixtures/php/${script}`, ...args);
}

// ---------------------------------------------------------------------------
// State

export interface ResetOptions {
  options?: Record<string, unknown>;
  /** Test clock for pickup/delivery dates, Europe/Zurich, e.g. "2026-11-11 10:00". */
  now?: string;
  flat_rate_type?: 'class' | 'order';
}

/**
 * Settings set A + overrides, empty SFTP inbox, empty Mailpit. Returns the QR
 * sequence number before the test. The default clock matches the golden files.
 */
export async function reset(opts: ResetOptions = {}): Promise<number> {
  const out = php('reset.php', JSON.stringify({ now: '2026-11-11 10:00', ...opts }));
  compose(['exec', '-T', 'sftp', 'sh', '-c', 'rm -f /home/WooCommerce/Eingang/*']);
  await fetch(`${MAILPIT_URL}/api/v1/messages`, { method: 'DELETE' });
  return JSON.parse(out).sequence;
}

export function setOption(name: string, value: unknown): void {
  wp('option', 'update', name, typeof value === 'string' ? value : JSON.stringify(value), ...(typeof value === 'string' ? [] : ['--format=json']));
}

// ---------------------------------------------------------------------------
// Orders

export interface Address {
  first_name: string;
  last_name: string;
  company?: string;
  address_1: string;
  city: string;
  postcode: string;
  country: string;
  phone?: string;
  email?: string;
}

export const ANNA: Address = {
  first_name: 'Anna', last_name: 'Beispiel', address_1: 'Bahnhofstrasse 12a', city: 'Zürich',
  postcode: '8001', country: 'CH', phone: '0441112233', email: 'anna.beispiel@example.test',
};
export const MARCO: Address = {
  first_name: 'Marco', last_name: 'Rossi', company: 'Beispiel GmbH', address_1: 'Via Cantonale 5', city: 'Lugano',
  postcode: '6900', country: 'CH', phone: '0912223344', email: 'marco.rossi@example.test',
};

export interface OrderInput {
  billing?: Address;
  /** null = no shipping address; omitted = same as billing. */
  shipping?: Partial<Address> | null;
  items?: [sku: string, qty: number][];
  shipping_method?: 'flat_rate' | 'free_shipping' | null;
  status?: string;
}

export function createOrder(input: OrderInput = {}): number {
  return Number(php('create-order.php', JSON.stringify({ billing: ANNA, ...input })));
}

export function setStatus(orderId: number, status: string): void {
  php('set-status.php', String(orderId), status);
}

export interface OrderInfo {
  id: number;
  status: string;
  tracking: string;
  notes: string[];
  sequence: number;
}

export function orderInfo(orderId: number): OrderInfo {
  return JSON.parse(php('order-info.php', String(orderId)));
}

export function orderEditUrl(orderId: number): string {
  return HPOS ? `/wp-admin/admin.php?page=wc-orders&action=edit&id=${orderId}` : `/wp-admin/post.php?post=${orderId}&action=edit`;
}

export function ordersListUrl(): string {
  return HPOS ? '/wp-admin/admin.php?page=wc-orders' : '/wp-admin/edit.php?post_type=shop_order';
}

// ---------------------------------------------------------------------------
// SFTP stand-in

export interface SftpFile {
  name: string;
  content: string;
}

export function sftpFiles(): SftpFile[] {
  const names = compose(['exec', '-T', 'sftp', 'sh', '-c', 'ls /home/WooCommerce/Eingang']).split('\n').filter(Boolean).sort();
  return names.map((name) => ({
    name,
    content: compose(['exec', '-T', 'sftp', 'cat', `/home/WooCommerce/Eingang/${name}`]),
  }));
}

/** Splits a Planzer CSV into its A1/P1/O1 lines, each as an array of fields. */
export function parseCsv(content: string): Record<'A1' | 'P1' | 'O1', string[]> {
  const lines = content.split('\r\n');
  const out: Record<string, string[]> = {};
  for (const line of lines) {
    const fields = line.split(';');
    out[fields[0]] = fields;
  }
  return out as Record<'A1' | 'P1' | 'O1', string[]>;
}

/** Field accessor: field(csv, 'A1_41'). */
export function field(csv: Record<'A1' | 'P1' | 'O1', string[]>, name: string): string {
  const [group, index] = name.split('_');
  return csv[group as 'A1' | 'P1' | 'O1'][Number(index)] ?? '';
}

/** Golden file with the placeholders filled in ({ORDER_ID}, {SEQ}, {SEQ9}). */
export function golden(name: string, orderId: number, sequence: number): string {
  return readFileSync(path.join(E2E_DIR, 'fixtures', 'golden', `${name}.csv`), 'utf8')
    .replaceAll('{ORDER_ID}', String(orderId))
    .replaceAll('{SEQ9}', String(sequence).padStart(9, '0'))
    .replaceAll('{SEQ}', String(sequence));
}

/** Package number as the plugin builds it: 91 + customer no. (6) + branch (3) + sequence (9). */
export function packageNumber(sequence: number): string {
  return `91123456057${String(sequence).padStart(9, '0')}`;
}

// ---------------------------------------------------------------------------
// Mailpit

export interface Mail {
  ID: string;
  Subject: string;
  To: { Address: string }[];
  Attachments: number;
}

export async function mails(): Promise<Mail[]> {
  const res = await fetch(`${MAILPIT_URL}/api/v1/messages`);
  return (await res.json()).messages;
}

/** Mails with the Planzer PDF (label/delivery note), oldest first. */
export async function planzerMails(): Promise<Mail[]> {
  return (await mails()).filter((m) => m.Subject.startsWith('Planzer')).reverse();
}

export async function mailHtml(id: string): Promise<string> {
  const res = await fetch(`${MAILPIT_URL}/api/v1/message/${id}`);
  return (await res.json()).HTML as string;
}

export async function attachment(id: string): Promise<Buffer> {
  const msg = await (await fetch(`${MAILPIT_URL}/api/v1/message/${id}`)).json();
  const part = msg.Attachments[0].PartID;
  return Buffer.from(await (await fetch(`${MAILPIT_URL}/api/v1/message/${id}/part/${part}`)).arrayBuffer());
}

// ---------------------------------------------------------------------------
// PDF (poppler-utils + zbar-tools in the wordpress container)

export interface PdfCheck {
  pages: number;
  /** Page size in mm, rounded. */
  widthMm: number;
  heightMm: number;
  text: string;
  /** Decoded QR codes on page 1. */
  qr: string[];
  /** Geometry of the first QR code on page 1, measured at 600 dpi. */
  qrGeometry?: { edge_mm: number; module_mm: number; quiet_modules: number };
}

export function inspectPdf(pdf: Buffer): PdfCheck {
  const out = compose(['exec', '-T', 'wordpress', 'sh', '-c', [
    'f=$(mktemp -d)',
    'cat > $f/n.pdf',
    'pdfinfo $f/n.pdf',
    'echo "@@TEXT@@"',
    'pdftotext -layout $f/n.pdf -',
    'echo "@@QR@@"',
    'pdftoppm -r 600 -png -f 1 -l 1 -singlefile $f/n.pdf $f/p',
    'zbarimg --raw -q $f/p.png 2>/dev/null || true',
    'echo "@@GEO@@"',
    'pts=$(zbarimg --xml -q $f/p.png 2>/dev/null | grep -o "points=\'[^\']*\'" | head -1 | tr -d "+\'" | sed "s/points=//")',
    'if [ -n "$pts" ]; then set -- $(echo "$pts" | tr "," " " | awk \'{minx=$1;maxx=$1;miny=$2;maxy=$2; for(i=1;i<=NF;i+=2){if($i<minx)minx=$i;if($i>maxx)maxx=$i;if($(i+1)<miny)miny=$(i+1);if($(i+1)>maxy)maxy=$(i+1)} print minx, miny, maxx, maxy}\'); php /e2e/fixtures/qr-geometry.php $f/p.png "$@"; fi',
    'rm -rf $f',
  ].join('; ')], pdf);
  const [info, rest] = out.split('@@TEXT@@');
  const [text, rest2] = rest.split('@@QR@@');
  const [qr, geo] = rest2.split('@@GEO@@');
  const size = /Page size:\s+([\d.]+) x ([\d.]+) pts/.exec(info)!;
  const pt2mm = (pt: string) => Math.round((Number(pt) * 25.4) / 72);
  return {
    pages: Number(/Pages:\s+(\d+)/.exec(info)![1]),
    widthMm: pt2mm(size[1]),
    heightMm: pt2mm(size[2]),
    text,
    qr: qr.split('\n').map((l) => l.replace(/\r$/, '')).filter((l) => l.trim() !== ''),
    qrGeometry: geo.trim() ? JSON.parse(geo.trim()) : undefined,
  };
}

/** QR content from PAKET V17: package number padded with spaces to 50 chars, then 8888888. */
export function expectedQr(sequence: number): string {
  return packageNumber(sequence) + ' '.repeat(30 + 20 - 7) + '8888888';
}

// ---------------------------------------------------------------------------
// Seeded IDs

let cachedIds: { products: Record<string, number>; classes: Record<string, number> } | undefined;

export function ids(): { products: Record<string, number>; classes: Record<string, number> } {
  cachedIds ??= JSON.parse(php('ids.php'));
  return cachedIds!;
}

/** The single CSV in the SFTP inbox, parsed. Fails if there is not exactly one. */
export function onlyCsv(): Record<'A1' | 'P1' | 'O1', string[]> {
  const files = sftpFiles();
  if (files.length !== 1) {
    throw new Error(`Expected one CSV on SFTP, found ${files.length}: ${files.map((f) => f.name).join(', ')}`);
  }
  return parseCsv(files[0].content);
}

/** Checkbox of an order row on the orders list (HPOS: id[], legacy: post[]). */
export function orderCheckbox(orderId: number): string {
  return HPOS ? `input[name="id[]"][value="${orderId}"]` : `input[name="post[]"][value="${orderId}"]`;
}

/**
 * Fields left out of the golden comparison because a known defect fills them (concept §6/§7).
 * The defect has its own test.fail() test in specs/known-defects.spec.ts.
 */
const GOLDEN_MASK: Record<string, string> = { A1_76: 'D10' };

/** CSV with the masked fields replaced by their defect ID, for comparing with a golden file. */
export function maskCsv(content: string): string {
  return content.split('\r\n').map((line) => {
    const fields = line.split(';');
    for (const [name, defect] of Object.entries(GOLDEN_MASK)) {
      const [group, index] = name.split('_');
      if (fields[0] === group && fields.length > Number(index)) fields[Number(index)] = `<${defect}>`;
    }
    return fields.join(';');
  }).join('\r\n');
}
