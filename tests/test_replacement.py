import unittest
import re

class TestEmDashRemoverV4(unittest.TestCase):
    TARGETS = [
        '—',            # Em dash
        '–',            # En dash
        '&mdash;',
        '&MDASH;',
        '&ndash;',
        '&NDASH;',
        '&#8212;',      # Em dash decimal
        '&#8211;',      # En dash decimal
        '&#x2014;',     # Em dash hex
        '&#x02014;',
        '&#X2014;',
        '&#X02014;',
        '&#x2013;',     # En dash hex
        '&#x02013;',
        '&#X2013;',
        '&#X02013;',
    ]
    REPLACEMENT = '-'
    PATTERN = r'(<!--.*?-->|<script\b[^>]*>.*?<\/script\s*>|<style\b[^>]*>.*?<\/style\s*>|<pre\b[^>]*>.*?<\/pre\s*>|<code\b[^>]*>.*?<\/code\s*>|<textarea\b[^>]*>.*?<\/textarea\s*>|<svg\b[^>]*>.*?<\/svg\s*>|<kbd\b[^>]*>.*?<\/kbd\s*>|<samp\b[^>]*>.*?<\/samp\s*>|<var\b[^>]*>.*?<\/var\s*>|<[^>]+>)'

    def process_html(self, html):
        if not html or not isinstance(html, str):
            return html

        if "<html" not in html.lower() and "<!doctype html" not in html.lower():
            return html

        if not any(t in html for t in self.TARGETS):
            return html

        protected = {}
        counter = 0

        def protect(match):
            nonlocal counter
            key = f"___EM_DASH_REMOVER_PROTECTED_{counter}___"
            protected[key] = match.group(0)
            counter += 1
            return key

        processed = re.sub(self.PATTERN, protect, html, flags=re.IGNORECASE | re.DOTALL)

        for t in self.TARGETS:
            processed = processed.replace(t, self.REPLACEMENT)

        if protected:
            for k, v in protected.items():
                processed = processed.replace(k, v)

        return processed

    def test_basic_em_and_en_dashes(self):
        text = "<html><body><p>AI text—has em dash and en dash – in here.</p></body></html>"
        expected = "<html><body><p>AI text-has em dash and en dash - in here.</p></body></html>"
        self.assertEqual(self.process_html(text), expected)

    def test_html_entities(self):
        text = "<html><body><p>&mdash; and &ndash; and &#8212; and &#8211; and &#x2014; and &#x2013;.</p></body></html>"
        expected = "<html><body><p>- and - and - and - and - and -.</p></body></html>"
        self.assertEqual(self.process_html(text), expected)

    def test_protected_tags_and_attributes(self):
        text = """<!DOCTYPE html><html><body>
<a href="https://example.com/item—1" title="Title — Here">Link — Text</a>
<script>var x = "do not touch — or –";</script>
<style>.cls { content: "—"; }</style>
<pre><code>console.log("—");</code></pre>
<textarea>User input — untouched</textarea>
<svg><text>—</text></svg>
<!-- Comment with — dash -->
</body></html>"""
        expected = """<!DOCTYPE html><html><body>
<a href="https://example.com/item—1" title="Title — Here">Link - Text</a>
<script>var x = "do not touch — or –";</script>
<style>.cls { content: "—"; }</style>
<pre><code>console.log("—");</code></pre>
<textarea>User input — untouched</textarea>
<svg><text>—</text></svg>
<!-- Comment with — dash -->
</body></html>"""
        self.assertEqual(self.process_html(text), expected)

    def test_non_html_skipped(self):
        raw_json = '{"data": "— value"}'
        self.assertEqual(self.process_html(raw_json), raw_json)

if __name__ == '__main__':
    unittest.main()
