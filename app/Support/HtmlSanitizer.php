<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Minimal allow-list HTML sanitiser for instructor-authored lesson content
 * (Blog / Text lessons written with the rich text editor).
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'hr', 'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'div',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="sf-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('sf-root') ?? $dom->documentElement;

        if (! $root) {
            return e(strip_tags($html));
        }

        self::sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Drop dangerous containers entirely, unwrap harmless unknown tags.
                    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'link', 'meta'], true)) {
                        $node->removeChild($child);

                        continue;
                    }

                    self::sanitizeChildren($child);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                self::sanitizeAttributes($child, $tag);
                self::sanitizeChildren($child);
            }
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        $names = [];
        foreach ($element->attributes as $attribute) {
            $names[] = $attribute->name;
        }

        foreach ($names as $name) {
            if (! in_array(strtolower($name), $allowed, true)) {
                $element->removeAttribute($name);

                continue;
            }

            if (in_array(strtolower($name), ['href', 'src'], true)) {
                $value = trim($element->getAttribute($name));
                $isSafe = preg_match('~^(https?://|/|#|mailto:)~i', $value) === 1;

                if (! $isSafe) {
                    $element->removeAttribute($name);
                }
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'noopener noreferrer');
            $element->setAttribute('target', '_blank');
        }
    }
}
