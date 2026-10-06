<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class Html
{
    /**
     * Tags a rich-text editor (Quill) is allowed to produce, each mapped to
     * the attributes it may carry. Anything else — scripts, styles, event
     * handlers, iframes, forms — is stripped entirely rather than escaped,
     * since this HTML is rendered unescaped wherever a task description is
     * shown to other users.
     *
     * @var array<string, array<int, string>>
     */
    protected const ALLOWED = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'h1' => [], 'h2' => [], 'h3' => [],
        'blockquote' => [], 'code' => [], 'pre' => [],
        'a' => ['href'],
        'span' => ['class'],
    ];

    /**
     * Strip a rich-text editor's HTML output down to a known-safe tag/attribute
     * whitelist using a real DOM parser (not regex), so markup can't be split
     * across tags to smuggle something past a naive string match. Any link
     * gets a safe-ish href scheme check and forced rel="noopener noreferrer".
     */
    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);

        if (! $root) {
            return null;
        }

        $clean = static::cleanChildren($dom, $root);
        $result = trim($clean);

        return $result === '' ? null : $result;
    }

    protected static function cleanChildren(DOMDocument $dom, DOMNode $node): string
    {
        $out = '';

        foreach (iterator_to_array($node->childNodes) as $child) {
            $out .= static::cleanNode($dom, $child);
        }

        return $out;
    }

    protected static function cleanNode(DOMDocument $dom, DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars($node->textContent, ENT_QUOTES | ENT_HTML5);
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = strtolower($node->tagName);
        $inner = static::cleanChildren($dom, $node);

        if (! array_key_exists($tag, static::ALLOWED)) {
            // Drop the tag but keep whatever safe content was inside it.
            return $inner;
        }

        $attrs = '';

        foreach (static::ALLOWED[$tag] as $attrName) {
            if (! $node->hasAttribute($attrName)) {
                continue;
            }

            $value = $node->getAttribute($attrName);

            if ($attrName === 'href') {
                if (! preg_match('/^(https?:|mailto:)/i', trim($value))) {
                    continue;
                }
            }

            $attrs .= ' '.$attrName.'="'.htmlspecialchars($value, ENT_QUOTES | ENT_HTML5).'"';
        }

        if ($tag === 'a') {
            $attrs .= ' target="_blank" rel="noopener noreferrer"';
        }

        if ($tag === 'br') {
            return '<br>';
        }

        return "<{$tag}{$attrs}>{$inner}</{$tag}>";
    }
}
