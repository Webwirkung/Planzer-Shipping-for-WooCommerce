# Deployment to staging

Every merge into `develop` deploys the plugin to the staging site https://playgroundria.kinsta.cloud
(`.github/workflows/deploy-staging.yml`). It can also be started by hand: Actions → "Deploy to staging" → Run workflow.

## What it does

1. Builds the plugin like a release with `tests/e2e/bin/build-plugin.sh` (composer without dev packages, admin assets).
2. Uploads it with `rsync` over SSH into `wp-content/plugins/wc-planzer-shipping.new`.
3. Puts the site into maintenance mode for the swap (`wc-planzer-shipping` → `.old`, `.new` → `wc-planzer-shipping`), so no request runs into a missing or half-uploaded plugin, ends maintenance mode (also when a step fails) and removes the old folder.
4. Checks that the plugin is active (activates it if not), prints its version, flushes the WordPress object cache and the Kinsta cache.

Pull requests into `develop` already ran the E2E tests, so the deployment doesn't run them again.

## Setup (once)

In MyKinsta, site "Playground Ria" → environment **Live** (https://playgroundria.kinsta.cloud, the one we test on; the Kinsta environment "Staging" is not used) → Info → "SFTP/SSH": host, port, username and the path of the site
(`/www/…/public`).

SSH key only for this deployment:

```bash
ssh-keygen -t ed25519 -N '' -C 'github-actions planzer-woocommerce staging' -f planzer-staging-deploy
```

- `planzer-staging-deploy.pub` → MyKinsta → User settings → SSH keys (of a MyKinsta user with access to the site).
- GitHub repo → Settings → Environments → `staging`:

| Where | Name | Value |
|---|---|---|
| Environment secret | `KINSTA_SSH_KEY` | Content of `planzer-staging-deploy` (private key, with the BEGIN/END lines) |
| Environment variable | `KINSTA_SSH_HOST` | Host (IP) from MyKinsta |
| Environment variable | `KINSTA_SSH_PORT` | SSH port from MyKinsta |
| Environment variable | `KINSTA_SSH_USER` | SSH username from MyKinsta |
| Environment variable | `KINSTA_SITE_PATH` | Path of the site, e.g. `/www/playgroundria_123/public` |
| Environment variable | `KINSTA_SSH_KNOWN_HOSTS` | Verified host key line(s), see below |

Only the private key is secret. Host, port, user and path are shown in MyKinsta, and the host key is public; only repo admins can change variables, as with secrets.

Host key: the workflow trusts only this key, so nobody can pose as the server. Get it once from a machine you trust and check the fingerprint against your own `~/.ssh/known_hosts` from an earlier SSH/SFTP login to the site (or ask Kinsta support):

```bash
ssh-keyscan -p <port> <host> 2>/dev/null | tee kinsta-known-hosts
ssh-keygen -lf kinsta-known-hosts      # compare the fingerprint
```

Paste the content of `kinsta-known-hosts` as the variable `KINSTA_SSH_KNOWN_HOSTS`.

Then delete both key files locally.
