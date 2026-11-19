# Deployment to staging

Every merge into `develop` deploys the plugin to the staging site https://playgroundria.kinsta.cloud
(`.github/workflows/deploy-staging.yml`). It can also be started by hand: Actions → "Deploy to staging" → Run workflow.

## What it does

1. Builds the plugin like a release with `tests/e2e/bin/build-plugin.sh` (composer without dev packages, admin assets).
2. Uploads it with `rsync` over SSH into `wp-content/plugins/wc-planzer-shipping.new`.
3. Swaps the folders (`wc-planzer-shipping` → `.old`, `.new` → `wc-planzer-shipping`), so the site never runs a half-uploaded plugin, and removes the old one.
4. Checks that the plugin is active (activates it if not), prints its version, flushes the WordPress object cache and the Kinsta cache.

Pull requests into `develop` already ran the E2E tests, so the deployment doesn't run them again.

## Setup (once)

In MyKinsta, site "playgroundria" → Info → "SFTP/SSH": host, port, username and the path of the site
(`/www/…/public`).

SSH key only for this deployment:

```bash
ssh-keygen -t ed25519 -N '' -C 'github-actions planzer-woocommerce staging' -f planzer-staging-deploy
```

- `planzer-staging-deploy.pub` → MyKinsta → User settings → SSH keys (of a MyKinsta user with access to the site).
- GitHub repo → Settings → Environments → `staging` → Environment secrets:

| Secret | Value |
|---|---|
| `KINSTA_SSH_HOST` | Host (IP) from MyKinsta |
| `KINSTA_SSH_PORT` | SSH port from MyKinsta |
| `KINSTA_SSH_USER` | SSH username from MyKinsta |
| `KINSTA_SITE_PATH` | Path of the site, e.g. `/www/playgroundria_123/public` |
| `KINSTA_SSH_KEY` | Content of `planzer-staging-deploy` (private key) |

Then delete both key files locally.
