# FINA wiki content corrections

Five corrections reviewed against the current production page source:

| Page | Change |
| --- | --- |
| [Literature Export](https://fina.oeaw.ac.at/Literature_Export) | Query `Category:Literature` for the CSV and BibTeX exports. |
| [Template:GraphFlowchart](https://fina.oeaw.ac.at/Template:GraphFlowchart) | Use plain page IDs without HTML links in Mermaid diagrams. |
| [Mermaid Test](https://fina.oeaw.ac.at/Mermaid_Test) | Supply the missing `N.N.` node ID in the existing click instruction. |
| [Form:Person](https://fina.oeaw.ac.at/Form:Person) | Use empty values when Wikidata returns no ID or description. |
| [Template:Query Person](https://fina.oeaw.ac.at/Template:Query_Person) | Use the same empty fallbacks for missing Wikidata results. |

These edits affect pages in the wiki database. Merging this directory does not
apply them: nothing runs during image build, startup or deployment. No database
snapshot is imported. `MediaWiki:Common.js` is not included.

## Preview and apply

Python 3 is sufficient; there are no additional dependencies. From the repository
root, preview the current target pages and exact diffs:

```sh
python3 upgrade/1.43/content/migrate-content.py --url https://fina.oeaw.ac.at
```

The tool checks the complete source hash of every selected page before making
any edits. Missing or changed pages stop the batch; already-corrected pages are
skipped. `reviewed_revision` in the patch file records the production revision
reviewed, while hashes allow the same source to be tested on a local wiki.

After reviewing the preview, apply explicitly with a private JSON credentials
file containing `username` and `password` for an account with edit permission:

```sh
python3 upgrade/1.43/content/migrate-content.py --url https://fina.oeaw.ac.at \
  --apply --credentials /path/to/private-credentials.json
```

Use `--page 'Literature Export'` to select one correction; repeat `--page` for
several. Each edit also supplies the revision read during preview to detect
concurrent changes. Edits are sequential, not a transaction: an error stops the
remaining edits, and rerunning skips corrections already applied.

Existing export columns and the 5,000-record limit are retained, as are the
existing graph links and successful Wikidata results. BibTeX retains its existing
field mappings and nonstandard type labels (for example, `Collective book`);
this correction does not normalize bibliographic metadata.

## Checks

```sh
python3 -m unittest discover -s upgrade/1.43/content/tests -v
```

Before applying to production, verify both literature downloads, the diagrams
on `Graph Correspondence` and `Mermaid Test`, and the person form/query with both
a known Wikidata person and a name without a match.
