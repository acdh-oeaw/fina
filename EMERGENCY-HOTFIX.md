# Emergency hotfix for MediaWiki 1.39

Based on main commit e945af6e2816a01816bcdd8d718572223fa87bc4.

ExternalData is disabled in LocalSettings.php and neither downloaded nor installed
in the image. ExternalData 3.7 fixes CVE-2026-100382 but requires MediaWiki 1.42+;
it must not be installed on this 1.39 image by lowering its version requirement.
Re-enabling it belongs to the separately tested MediaWiki 1.43 migration.

The production image removes require-dev and autoload-dev from each standalone
Composer project without a lock file before dependency resolution. Projects
with locks are unchanged. This removes the PHP_CodeSniffer solver conflict
without adding a security-advisory exemption for SemanticMediaWiki or the other
extension/skin projects. SemanticMediaWiki's known root version is set to 4.2.0.
Existing broad advisory exceptions for core and Mpdf, platform overrides, and
unlocked runtime dependencies remain technical debt; this is not a full security
upgrade or a reproducible dependency lock update.

## Follow-up: SMW runtime security advisories

The next solver error in SemanticResultFormats concerns actual SMW runtime
advisories, not development requirements. SRF now uses a local path repository
symlink to the pinned SMW 4.2.0 directory already installed by the image, instead
of downloading and deleting a second SMW copy with potentially different code.
The image removes registration of the smwtask API module from SMW 4.2.0's actual
ApiModuleManager hook. A build check exercises that hook and verifies that
smwtask is absent while ask, askargs and smwbrowse remain registered.
This blocks the affected API endpoint and also disables administrative workflows
that rely on that endpoint. Other SMW XSS/open-redirect advisories are NOT fixed.

Only eight known advisory IDs affecting 4.2.0 are exempted from blocking in the
SRF Composer project. Audit reporting remains enabled, and all other advisories
remain blocked there. The helper refuses to run if SMW_VERSION changes from
4.2.0. This is a temporary legacy build exception, not a patched SMW release;
do not describe the image as fully secured. A supported upgrade or reviewed
backport is still required for the remaining runtime vulnerabilities.

The smwtask mitigation is implemented in source because SMW 4.2.0 registers
the module dynamically through ApiMain::moduleManager; merely unsetting
wgAPIModules in LocalSettings does not cover that registration path.

Inspect the remaining advisories after a successful local build:

```bash
podman run --rm --entrypoint composer localhost/fina:emergency-hotfix \
  --working-dir=/var/www/html/extensions/SemanticResultFormats audit --no-dev
```

An audit failure is expected until the remaining advisories are fixed. It is
not evidence that the source mitigation failed; verify the running application
separately, including API help/module discovery without executing a task.

References:
- https://github.com/advisories/GHSA-jr78-w6w5-m8f8
- https://packagist.org/api/security-advisories/?packages[]=mediawiki/semantic-media-wiki
- https://getcomposer.org/doc/06-config.md#ignore-id
- https://getcomposer.org/doc/05-repositories.md#path

## Validation and rollout

1. Run the Emergency image build check workflow on the hotfix branch, or run
   `docker build --pull --progress=plain -t fina:emergency-hotfix .` locally.
   The new workflow does not publish or deploy. The existing starter workflow
   can deploy; do not use it merely to test the build.
2. Back up the database and uploads before deployment. Verify the deployment
   uses this image and does not mount an old LocalSettings.php over the new one.
3. After deployment, confirm External Data is absent from Special:Version.
   Check representative pages, templates, forms, tables and maps. The public API
   review on 2026-09-30 found actual usage in Form:Person (Wikidata lookup and
   suggested WikidataID values), Form:Institution (country choices fetched from
   an EU XML resource), and Template:Query Person (Wikidata identity/description).
   Disabling ExternalData affects these features; their fields may need manual
   input or temporary replacement content. The review covered all 106 current
   public pages in Template, Form, Module, Widget and MediaWiki namespaces, not
   all 16,098 wiki pages, deleted content or older revisions.

## Existing exposure

Disabling the extension prevents further use of it; it does not remove a web
shell already written into persistent uploads. Preserve and inspect uploads,
container changes, access logs and application logs. Check for unexpected PHP,
PHTML, PHAR and .htaccess files, including names other than Nx_mj12.php.
If compromise is suspected, isolate the instance, preserve evidence, rebuild
from trusted sources, inspect persistent data, and rotate database credentials
and other exposed secrets from a trusted environment.

References:
- https://www.mediawiki.org/wiki/Extension:External_Data
- https://www.mediawiki.org/wiki/Extension:External_Data/Version_history
- https://www.mail-archive.com/mediawiki-l@lists.wikimedia.org/msg20197.html
- https://getcomposer.org/doc/03-cli.md#install-i
