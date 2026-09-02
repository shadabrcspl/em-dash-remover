import unittest
import re

class TestEmDashRemover(unittest.TestCase):
    TARGETS = [
        '—',            # UTF-8 Literal
        '&mdash;',      # HTML named entity lower
        '&MDASH;',      # HTML named entity upper
        '&#8212;',      # Decimal entity
        '&#x2014;',     # Hex entity lower
        '&#x02014;',    # Hex entity padded
        '&#X2014;',     # Hex entity upper
        '&#X02014;',    # Hex entity padded upper
    ]
    REPLACEMENT = '-'
    SPLIT_PATTERN = r'(<!--.*?-->|<script\b[^>]*>.*?<\/script\s*>|<style\b[^>]*>.*?<\/style\s*>|<pre\b[^>]*>.*?<\/pre\s*>|<code\b[^>]*>.*?<\/code\s*>|<textarea\b[^>]*>.*?<\/textarea\s*>|<svg\b[^>]*>.*?<\/svg\s*>|<kbd\b[^>]*>.*?<\/kbd\s*>|<samp\b[^>]*>.*?<\/samp\s*>|<var\b[^>]*>.*?<\/var\s*>|<[^>]+>)'

    def replace_rendered_text(self, html, targets=None, replacement=None):
        if not html or not isinstance(html, str):
            return html
        
        if targets is None:
            targets = self.TARGETS
        if replacement is None:
            replacement = self.REPLACEMENT

        found = any(t in html for t in targets)
        if not found:
            return html

        parts = re.split(self.SPLIT_PATTERN, html, flags=re.IGNORECASE | re.DOTALL)
        if not parts:
            return html

        for i, part in enumerate(parts):
            if not part or part.startswith('<'):
                continue
            for t in targets:
                part = part.replace(t, replacement)
            parts[i] = part

        return "".join(parts)

    def test_basic_em_dash_replacement(self):
        text = "<p>Artificial intelligence—especially large language models—is evolving.</p>"
        expected = "<p>Artificial intelligence-especially large language models-is evolving.</p>"
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_all_html_entities(self):
        text = "<p>One&mdash;two&#8212;three&#x2014;four&MDASH;five&#x02014;six&#X2014;seven.</p>"
        expected = "<p>One-two-three-four-five-six-seven.</p>"
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_protected_scripts_and_styles(self):
        text = '<script>const dash = "—";</script><style>.cls::before { content: "—"; }</style><p>Visible—Text</p>'
        expected = '<script>const dash = "—";</script><style>.cls::before { content: "—"; }</style><p>Visible-Text</p>'
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_protected_code_pre_textarea_svg(self):
        text = (
            '<pre><code>print("—")</code></pre>'
            '<textarea>Raw — text</textarea>'
            '<svg><text>Icon —</text></svg>'
            '<kbd>Ctrl—C</kbd>'
            '<div>Replaced — Content</div>'
        )
        expected = (
            '<pre><code>print("—")</code></pre>'
            '<textarea>Raw — text</textarea>'
            '<svg><text>Icon —</text></svg>'
            '<kbd>Ctrl—C</kbd>'
            '<div>Replaced - Content</div>'
        )
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_html_tag_attributes_preserved(self):
        text = '<a href="https://example.com/ai—tools" title="Title — Here" data-custom="value—1">Link — Text</a>'
        expected = '<a href="https://example.com/ai—tools" title="Title — Here" data-custom="value—1">Link - Text</a>'
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_html_comments_preserved(self):
        text = '<!-- Comment with — dash --><span>Body — text</span>'
        expected = '<!-- Comment with — dash --><span>Body - text</span>'
        self.assertEqual(self.replace_rendered_text(text), expected)

    def test_empty_and_no_match(self):
        self.assertEqual(self.replace_rendered_text(""), "")
        self.assertEqual(self.replace_rendered_text("<p>No dashes here.</p>"), "<p>No dashes here.</p>")

if __name__ == '__main__':
    unittest.main()
