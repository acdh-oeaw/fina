"""Preview optional FINA content corrections; write only with explicit --apply."""
import argparse
import difflib
import hashlib
import http.cookiejar
import json
import os
from pathlib import Path
import sys
import urllib.parse
import urllib.request


class Wiki:
    def __init__(self, base):
        self.base = base.rstrip('/')
        self.opener = urllib.request.build_opener(
            urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def api(self, **params):
        data = urllib.parse.urlencode(dict(params, format='json', formatversion=2)).encode()
        with self.opener.open(self.base + '/api.php', data=data, timeout=90) as response:
            result = json.load(response)
        if 'error' in result:
            raise RuntimeError(result['error'])
        return result

    def login(self, credentials):
        token = self.api(action='query', meta='tokens', type='login')['query']['tokens']['logintoken']
        result = self.api(action='login', lgname=credentials['username'],
                          lgpassword=credentials['password'], lgtoken=token)
        if result['login']['result'] != 'Success':
            raise RuntimeError('Login failed')


def inspect(wiki, patches):
    """Read every selected page before deciding whether any edit can be made."""
    plan = []
    for patch in patches:
        page = wiki.api(action='query', prop='revisions', titles=patch['title'],
                        rvprop='content|ids', rvslots='main')['query']['pages'][0]
        item = {'patch': patch, 'status': 'MISSING'}
        if not page.get('missing') and not page.get('invalid'):
            revision = next(iter(page.get('revisions', [])), {})
            current = revision.get('slots', {}).get('main', {}).get('content')
            item['status'] = 'CHANGED'
            if isinstance(current, str):
                digest = hashlib.sha256(current.encode()).hexdigest()
                if digest == patch['after_sha256']:
                    item['status'] = 'ALREADY APPLIED'
                elif digest == patch['before_sha256']:
                    updated = current
                    for replacement in patch['replacements']:
                        if updated.count(replacement['before']) != replacement['count']:
                            raise RuntimeError('Invalid replacement count in patch: ' + patch['title'])
                        updated = updated.replace(replacement['before'], replacement['after'])
                    if hashlib.sha256(updated.encode()).hexdigest() != patch['after_sha256']:
                        raise RuntimeError('Invalid expected result in patch: ' + patch['title'])
                    item.update(status='APPLICABLE', current=current, updated=updated,
                                revid=revision['revid'])
        plan.append(item)
    return plan


def run(wiki, patches, apply=False, username=None):
    plan = inspect(wiki, patches)
    for item in plan:
        title = item['patch']['title']
        print(item['status'] + ': ' + title)
        if item['status'] == 'APPLICABLE':
            print(''.join(difflib.unified_diff(
                item['current'].splitlines(keepends=True),
                item['updated'].splitlines(keepends=True),
                fromfile=title + ' (current)', tofile=title + ' (proposed)')))
    if any(item['status'] in ('MISSING', 'CHANGED') for item in plan):
        print('No edits made. Review missing/changed pages; use --page to select independently reviewed corrections.')
        return 1
    if not apply:
        print('Preview only: no edits made. Use --apply after reviewing the target and diffs.')
        return 0
    pending = [item for item in plan if item['status'] == 'APPLICABLE']
    if not pending:
        print('No edits needed: selected corrections are already applied.')
        return 0
    if not username:
        raise RuntimeError('An authenticated username is required to apply corrections')
    token = wiki.api(action='query', meta='tokens')['query']['tokens']['csrftoken']
    for item in pending:
        patch = item['patch']
        result = wiki.api(action='edit', title=patch['title'], text=item['updated'],
                          baserevid=item['revid'], token=token, assertuser=username,
                          summary='FINA 1.43: ' + patch['reason'])
        if result['edit']['result'] != 'Success':
            raise RuntimeError('Edit failed: ' + patch['title'])
        print('Updated:', patch['title'], 'revision', result['edit']['newrevid'])
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--url', default=os.environ.get('FINA_URL'), help='Explicit target wiki URL (or FINA_URL)')
    parser.add_argument('--apply', action='store_true', help='Apply matching corrections after the complete preview')
    parser.add_argument('--page', action='append', help='Review/apply only this title; may be repeated')
    parser.add_argument('--credentials', default=os.environ.get('FINA_REVIEWER_FILE'),
                        help='Private JSON username/password file; required for --apply')
    args = parser.parse_args()
    if not args.url:
        parser.error('Specify --url or FINA_URL; there is no implicit target')
    url = urllib.parse.urlsplit(args.url)
    if url.scheme not in ('http', 'https') or not url.hostname or url.username or url.password or url.query or url.fragment:
        parser.error('Use an HTTP(S) wiki URL without credentials, query or fragment')
    if args.apply and not args.credentials:
        parser.error('--apply requires --credentials or FINA_REVIEWER_FILE')
    patches = json.loads(Path(__file__).with_name('content-patches.json').read_text())
    if args.page:
        unknown = set(args.page) - {p['title'] for p in patches}
        if unknown:
            parser.error('No reviewed correction for: ' + ', '.join(sorted(unknown)))
        patches = [p for p in patches if p['title'] in args.page]
    wiki = Wiki(args.url)
    credentials = None
    if args.credentials:
        credentials = json.loads(Path(args.credentials).read_text())
        wiki.login(credentials)
    print('Target:', wiki.base)
    return run(wiki, patches, args.apply, credentials['username'] if credentials else None)


if __name__ == '__main__':
    try:
        sys.exit(main())
    except (RuntimeError, OSError, KeyError, ValueError) as error:
        print('Stopped:', error, file=sys.stderr)
        sys.exit(1)
