#!/usr/bin/env node
// Turns the Playwright JSON report into a Markdown summary (GitHub job summary in CI).
//   node bin/summary.mjs [reports/results.json] [title]
import { readFileSync } from 'node:fs';

const [file = 'reports/results.json', title = 'E2E tests'] = process.argv.slice(2);
let report;
try {
  report = JSON.parse(readFileSync(file, 'utf8'));
} catch {
  console.log(`### ${title}\n\n❌ No test report (${file}). The run stopped before or during the tests, see the log.`);
  process.exit(0);
}

// status per test: expected | unexpected | flaky | skipped
const rows = new Map();
const defects = [];
const failures = [];

function walk(suite, file) {
  for (const spec of suite.specs ?? []) {
    for (const test of spec.tests) {
      const row = rows.get(file) ?? { tests: 0, expected: 0, unexpected: 0, flaky: 0, skipped: 0 };
      row.tests++;
      row[test.status]++;
      rows.set(file, row);
      const fail = test.annotations.find((a) => a.type === 'fail');
      if (fail && test.expectedStatus === 'failed') defects.push({ id: (fail.description ?? '').split(' ')[0], title: spec.title, ok: test.status !== 'unexpected' });
      if (test.status === 'unexpected') failures.push(`${file} › ${spec.title}`);
    }
  }
  for (const child of suite.suites ?? []) walk(child, file);
}
for (const suite of report.suites) walk(suite, suite.file.replace(/\.spec\.ts$/, ''));

const s = report.stats;
const minutes = (s.duration / 60000).toFixed(1);
const total = [...rows.values()].reduce((n, r) => n + r.tests, 0);
const icon = s.unexpected ? '❌' : '✅';
const out = [];

out.push(`### ${icon} ${title}: ${s.expected} passed, ${s.unexpected} failed, ${s.flaky} flaky, ${s.skipped} skipped (${total} tests, ${minutes} min)`);
out.push('', '| Area | Tests | Passed | Failed | Flaky | Skipped |', '|---|---:|---:|---:|---:|---:|');
for (const [area, r] of [...rows].sort()) {
  out.push(`| ${r.unexpected ? '❌' : '✅'} ${area} | ${r.tests} | ${r.expected} | ${r.unexpected} | ${r.flaky} | ${r.skipped} |`);
}

if (failures.length) {
  out.push('', '**Failed**', '', ...failures.map((f) => `- ${f}`));
}

if (defects.length) {
  const open = [...new Set(defects.filter((d) => d.ok).map((d) => d.id))].sort((a, b) => a.localeCompare(b, 'en', { numeric: true }));
  const fixed = defects.filter((d) => !d.ok);
  out.push('', `**Known defects still present (${open.length}):** ${open.join(', ')} — see docs/testing-concept.md.`);
  if (fixed.length) {
    out.push('', '**Known defects that look fixed** (remove `test.fail()`):', '', ...fixed.map((d) => `- ${d.id}: ${d.title}`));
  }
}

console.log(out.join('\n'));
