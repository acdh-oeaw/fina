"""Protect target wiki edits independently of any historical FINA dataset."""
import contextlib
import hashlib
import importlib.util
import io
from pathlib import Path
import unittest

spec = importlib.util.spec_from_file_location('content_migration',
    Path(__file__).resolve().parents[1] / 'migrate-content.py')
migration = importlib.util.module_from_spec(spec)
spec.loader.exec_module(migration)


def patch(title):
    return dict(title=title, reason='Test correction',
                replacements=[dict(before='old', after='new', count=1)],
                before_sha256=hashlib.sha256(b'old').hexdigest(),
                after_sha256=hashlib.sha256(b'new').hexdigest())


class Wiki:
    def __init__(self, pages, conflict=None):
        self.pages = pages
        self.conflict = conflict
        self.calls = []

    def api(self, **params):
        self.calls.append(params)
        if params['action'] == 'edit':
            if params['title'] == self.conflict:
                raise RuntimeError('editconflict')
            self.pages[params['title']] = params['text']
            return {'edit': {'result': 'Success', 'newrevid': 101}}
        if params.get('meta') == 'tokens':
            return {'query': {'tokens': {'csrftoken': 'test-token'}}}
        title = params['titles']
        page = {'title': title, 'missing': True}
        if title in self.pages:
            page = {'title': title, 'revisions': [{'revid': 100,
                    'slots': {'main': {'content': self.pages[title]}}}]}
        return {'query': {'pages': [page]}}

    def edits(self):
        return [call for call in self.calls if call['action'] == 'edit']


class ContentMigrationTest(unittest.TestCase):
    def run_plan(self, wiki, patches, apply=False):
        with contextlib.redirect_stdout(io.StringIO()) as output:
            code = migration.run(wiki, patches, apply, username='Reviewer')
        return code, output.getvalue()

    def test_default_preview_shows_diff_without_edits(self):
        wiki = Wiki({'A': 'old'})
        code, output = self.run_plan(wiki, [patch('A')])
        self.assertEqual(code, 0)
        self.assertIn('APPLICABLE: A', output)
        self.assertIn('+++ A (proposed)', output)
        self.assertEqual(wiki.pages['A'], 'old')
        self.assertEqual(wiki.edits(), [])

    def test_changed_later_page_prevents_every_edit(self):
        wiki = Wiki({'A': 'old', 'B': 'old plus a later edit'})
        code, output = self.run_plan(wiki, [patch('A'), patch('B')], True)
        self.assertEqual(code, 1)
        self.assertIn('CHANGED: B', output)
        self.assertEqual(wiki.edits(), [])
        self.assertEqual(wiki.pages['B'], 'old plus a later edit')

    def test_missing_later_page_prevents_every_edit(self):
        wiki = Wiki({'A': 'old'})
        code, output = self.run_plan(wiki, [patch('A'), patch('B')], True)
        self.assertEqual(code, 1)
        self.assertIn('MISSING: B', output)
        self.assertEqual(wiki.edits(), [])

    def test_already_applied_is_noop(self):
        wiki = Wiki({'A': 'new'})
        code, output = self.run_plan(wiki, [patch('A')], True)
        self.assertEqual(code, 0)
        self.assertIn('ALREADY APPLIED: A', output)
        self.assertEqual(wiki.edits(), [])

    def test_apply_reads_all_pages_then_uses_revision_guards(self):
        wiki = Wiki({'A': 'old', 'B': 'old'})
        code, _ = self.run_plan(wiki, [patch('A'), patch('B')], True)
        self.assertEqual(code, 0)
        self.assertEqual([c.get('titles') for c in wiki.calls[:2]], ['A', 'B'])
        self.assertEqual(wiki.pages, {'A': 'new', 'B': 'new'})
        for edit in wiki.edits():
            self.assertEqual(edit['baserevid'], 100)
            self.assertEqual(edit['assertuser'], 'Reviewer')
        self.run_plan(wiki, [patch('A'), patch('B')], True)
        self.assertEqual(len(wiki.edits()), 2)

    def test_edit_conflict_stops_without_retrying_or_editing_next_page(self):
        wiki = Wiki({'A': 'old', 'B': 'old'}, conflict='A')
        with self.assertRaisesRegex(RuntimeError, 'editconflict'):
            self.run_plan(wiki, [patch('A'), patch('B')], True)
        self.assertEqual([e['title'] for e in wiki.edits()], ['A'])
        self.assertEqual(wiki.pages, {'A': 'old', 'B': 'old'})

    def test_invalid_expected_hash_prevents_edits(self):
        wiki = Wiki({'A': 'old'})
        bad = patch('A')
        bad['after_sha256'] = 'invalid'
        with self.assertRaisesRegex(RuntimeError, 'Invalid expected result'):
            self.run_plan(wiki, [bad], True)
        self.assertEqual(wiki.edits(), [])

    def test_invalid_replacement_count_prevents_edits(self):
        wiki = Wiki({'A': 'old'})
        bad = patch('A')
        bad['replacements'][0]['count'] = 2
        with self.assertRaisesRegex(RuntimeError, 'Invalid replacement count'):
            self.run_plan(wiki, [bad], True)
        self.assertEqual(wiki.edits(), [])

    def test_multiple_replacements_are_one_revision_guarded_edit(self):
        wiki = Wiki({'A': 'old Qid Description'})
        correction = patch('A')
        correction.update(
            before_sha256=hashlib.sha256(b'old Qid Description').hexdigest(),
            after_sha256=hashlib.sha256(b'old Qid| Description|').hexdigest(),
            replacements=[dict(before=value, after=value + '|', count=1)
                          for value in ['Qid', 'Description']])
        code, _ = self.run_plan(wiki, [correction], True)
        self.assertEqual(code, 0)
        self.assertEqual(wiki.pages['A'], 'old Qid| Description|')
        self.assertEqual(len(wiki.edits()), 1)
        self.assertEqual(wiki.edits()[0]['baserevid'], 100)


if __name__ == '__main__':
    unittest.main()
