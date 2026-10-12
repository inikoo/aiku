<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Services\Seo;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\XPath;

/**
 * What a writer needs from someone else's page: its title, its headings in order, how long its
 * content is and how it starts. Menus, footers, forms and scripts are left out before the words
 * are counted, so the length is the page's content, not its template.
 */
class ReadPage
{
    private const string NOT_CONTENT = 'script, style, noscript, template, svg, nav, header, footer, aside, form, iframe, [aria-hidden="true"]';

    private const int MAX_HEADINGS = 40;

    private const int MAX_HEADING_LENGTH = 160;

    /**
     * @param  array<int, array{level: int, text: string}>  $headings
     */
    private function __construct(
        public readonly ?string $title,
        public readonly ?string $metaDescription,
        public readonly array $headings,
        public readonly int $wordCount,
        public readonly string $text
    ) {
    }

    public static function parse(string $html): self
    {
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        $title           = self::clean($document->querySelector('head title')?->textContent);
        $metaDescription = null;

        foreach ($document->querySelectorAll('meta[name]') as $meta) {
            if (strtolower(trim($meta->getAttribute('name'))) === 'description') {
                $metaDescription ??= self::clean($meta->getAttribute('content'));
            }
        }

        foreach ($document->querySelectorAll(self::NOT_CONTENT) as $element) {
            $element->parentNode?->removeChild($element);
        }

        $content  = $document->querySelector('main, [role="main"], article') ?? $document->body;
        $headings = [];

        foreach (($content ?? $document)->querySelectorAll('h1, h2, h3') as $heading) {
            $text = self::text($document, $heading);

            if ($text !== null && count($headings) < self::MAX_HEADINGS) {
                $headings[] = ['level' => (int) substr($heading->tagName, 1), 'text' => mb_substr($text, 0, self::MAX_HEADING_LENGTH)];
            }
        }

        $text = $content instanceof Element ? (self::text($document, $content) ?? '') : '';

        return new self(
            title: $title,
            metaDescription: $metaDescription,
            headings: $headings,
            wordCount: $text === '' ? 0 : count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY)),
            text: $text
        );
    }

    /**
     * The text of an element with a space between its text nodes, so two paragraphs or table cells
     * do not run into one word.
     */
    private static function text(HTMLDocument $document, Element $element): ?string
    {
        $parts = [];

        foreach (new XPath($document)->query('.//text()', $element) as $node) {
            $parts[] = $node->textContent;
        }

        return self::clean(implode(' ', $parts));
    }

    private static function clean(?string $value): ?string
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));

        return $value === '' ? null : $value;
    }
}
