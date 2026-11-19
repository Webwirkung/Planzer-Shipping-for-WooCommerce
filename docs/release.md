# Release

1. On `develop`: raise `Version` in `wc-planzer-shipping.php`, `Stable tag` in `readme.txt` (and `README.md`) and add the changelog entry `= 1.0.37 YYYY-MM-DD =` to `readme.txt`.
2. Open the release PR `develop` → `master` titled «Release DD.MM.YYYY». Its description is the summary for the consultants (1–3 sentences) at the top of the GitHub release.
3. Merge it. `.github/workflows/release.yml` then creates:
   - the tag `v.<Version>` (as `v.1.0.36`),
   - the GitHub release «Release DD.MM.YYYY · <Version>» with the PR description, the changelog entry from `readme.txt` and the pull requests since the previous tag,
   - the plugin zip `wc-planzer-shipping-<Version>.zip` as release asset (same build as the E2E tests and staging).
4. Deploy with Buddy.

If the tag already exists (version not raised), the workflow fails and creates nothing: raise the version, merge into `master` again.
