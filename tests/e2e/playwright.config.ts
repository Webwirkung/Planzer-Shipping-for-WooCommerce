import { defineConfig } from '@playwright/test';
import { BASE_URL, HPOS } from './lib/env';

// All tests share one WordPress, one SFTP inbox and one QR counter, so they run one at a time.
export default defineConfig({
  testDir: './specs',
  fullyParallel: false,
  workers: 1,
  timeout: 60_000,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',
  globalSetup: './lib/global-setup.ts',
  use: {
    baseURL: BASE_URL,
    storageState: '.auth/admin.json',
    actionTimeout: 15_000,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  metadata: { orderStorage: HPOS ? 'HPOS' : 'legacy' },
});
