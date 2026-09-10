<?php

namespace App\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Konversi HTML dari TinyMCE ke OOXML (Word XML) yang dapat diinjeksikan ke dalam template .docx.
 * Mendukung: bold, italic, underline, strikethrough, color, font-size, highlight/shading,
 *            alignment, indent, superscript, subscript, ordered/unordered list, LaTeX→OMML.
 */
class HtmlToOoxml
{
    private const BLOCK_ELEMENTS = ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ol', 'ul', 'table'];

    private const HIGHLIGHT_MAP = [
        'FFFF00' => 'yellow', 'FF0000' => 'red', '00FF00' => 'green',
        '0000FF' => 'blue', '00FFFF' => 'cyan', 'FF00FF' => 'magenta',
        'FFA500' => 'darkYellow', '000000' => 'black', 'FFFFFF' => 'white',
        '808080' => 'darkGray', 'C0C0C0' => 'lightGray', '008000' => 'darkGreen',
        '000080' => 'darkBlue', '008080' => 'darkCyan', '800080' => 'darkMagenta',
        '800000' => 'darkRed',
    ];

    public static function convert(string $html): string
    {
        if (empty(trim(strip_tags($html)))) {
            return '<w:p><w:r><w:t>-</w:t></w:r></w:p>';
        }
        
        $result = (new self())->parse($html);
        
        // Ensure the final output ends with a paragraph to prevent corrupted Word documents
        // especially when the HTML contains tables (<w:tbl>) that replace the final <w:p> in a table cell.
        if (!str_ends_with(trim($result), '</w:p>')) {
            $result .= '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr></w:p>';
        }
        
        return $result;
    }

    private function parse(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NOERROR);
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) return '<w:p><w:r><w:t>-</w:t></w:r></w:p>';

        $result = '';
        $pendingRuns = '';

        foreach ($body->childNodes as $node) {
            $isBlock = ($node instanceof DOMElement)
                && in_array(strtolower($node->tagName), self::BLOCK_ELEMENTS, true);

            if ($isBlock) {
                if ($pendingRuns !== '') {
                    $result .= '<w:p>' . $pendingRuns . '</w:p>';
                    $pendingRuns = '';
                }
                $result .= $this->processNode($node, []);
            } else {
                $pendingRuns .= $this->processNode($node, []);
            }
        }

        if ($pendingRuns !== '') {
            $result .= '<w:p>' . $pendingRuns . '</w:p>';
        }

        // Post-process: convert LaTeX delimiters \(...\) to OMML
        $result = $this->convertLatexToOmml($result);

        return $result ?: '<w:p><w:r><w:t>-</w:t></w:r></w:p>';
    }

    private function processNode(DOMNode $node, array $runCtx): string
    {
        if ($node instanceof DOMText) {
            $text = preg_replace('/\s+/', ' ', $node->nodeValue);
            if (trim($text) === '') return '';

            // Check for explicit delimiters: \(...\) or \[...\] or $$...$$
            $pattern = '/(\\\\\(.+?\\\\\)|\\\\\[.+?\\\\\]|\$\$.+?\$\$)/s';
            if (preg_match($pattern, $text)) {
                $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
                $out = '';
                foreach ($parts as $part) {
                    if (preg_match('/^\\\\\((.+?)\\\\\)$/s', $part, $m) || 
                        preg_match('/^\\\\\[(.+?)\\\\\]$/s', $part, $m) || 
                        preg_match('/^\$\$(.+?)\$\$$/s', $part, $m)) {
                        $out .= $this->latexToOmml(trim($m[1]));
                    } else {
                        if (trim($part) !== '') {
                            $out .= $this->makeRun($this->escXml($part), $runCtx);
                        }
                    }
                }
                return $out;
            }

            return $this->makeRun($this->escXml($text), $runCtx);
        }

        if (!($node instanceof DOMElement)) return '';

        $tag  = strtolower($node->tagName);
        $style = $node->getAttribute('style');
        $parsed = $this->parseStyle($style);

        return match (true) {
            // Block elements
            in_array($tag, ['p', 'div']) => $this->processBlock($node, $runCtx, $parsed),
            in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) =>
                $this->processBlock($node, array_merge($runCtx, ['bold' => true]), $parsed),
            $tag === 'blockquote' => $this->processBlock($node, $runCtx, array_merge($parsed, ['padding-left' => '720twips'])),
            $tag === 'table' => $this->processTable($node, $runCtx, $parsed),
            $tag === 'br' => '<w:r><w:br/></w:r>',

            // Lists
            $tag === 'ol' => $this->processOrderedList($node, array_merge($runCtx, $this->styleToCtx($parsed))),
            $tag === 'ul' => $this->processUnorderedList($node, array_merge($runCtx, $this->styleToCtx($parsed))),

            // Inline formatting
            in_array($tag, ['strong', 'b']) => $this->processInline($node, array_merge($runCtx, ['bold' => true])),
            in_array($tag, ['em', 'i'])     => $this->processInline($node, array_merge($runCtx, ['italic' => true])),
            $tag === 'u'  => $this->processInline($node, array_merge($runCtx, ['underline' => true])),
            $tag === 'sup' => $this->processInline($node, array_merge($runCtx, ['vertAlign' => 'superscript'])),
            $tag === 'sub' => $this->processInline($node, array_merge($runCtx, ['vertAlign' => 'subscript'])),
            $tag === 'span' => $this->processInline($node, array_merge($runCtx, $this->styleToCtx($parsed))),
            $tag === 'a' => $this->processLink($node, $runCtx),

            $tag === 'img' => $this->processImage($node, $runCtx),
            // Skip media
            in_array($tag, ['thead', 'tbody', 'tr', 'td', 'th', 'figure']) => '',

            // Default: recurse inline
            default => $this->processInline($node, $runCtx),
        };
    }

    // ── Block element ─────────────────────────────────────────
    private function processBlock(DOMElement $node, array $runCtx, array $parsedStyle): string
    {
        $align      = $parsedStyle['text-align'] ?? null;
        $paddingLeft = $parsedStyle['padding-left'] ?? $parsedStyle['margin-left'] ?? null;
        $indent     = $paddingLeft ? $this->cssLengthToTwips($paddingLeft) : null;

        $runs = '';
        foreach ($node->childNodes as $child) {
            $runs .= $this->processNode($child, $runCtx);
        }

        return $this->makeParagraph($runs, $align, $indent);
    }

    private function processInline(DOMElement $node, array $runCtx): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            $result .= $this->processNode($child, $runCtx);
        }
        return $result;
    }

    private function processLink(DOMElement $node, array $runCtx): string
    {
        $href = $node->getAttribute('href');
        $ctx  = array_merge($runCtx, ['underline' => true, 'color' => '0563C1']);
        $runs = $this->processInline($node, $ctx);
        if ($href) {
            $runs .= $this->makeRun($this->escXml(" ({$href})"), array_merge($runCtx, ['color' => '595959']));
        }
        return $runs;
    }

    private function processImage(DOMElement $node, array $runCtx): string
    {
        $latex = $node->getAttribute('data-latex');
        if ($latex) {
            $latex = trim($latex);
            if (preg_match('/^\$\$(.+?)\$\$$/s', $latex, $m)) $latex = trim($m[1]);
            elseif (preg_match('/^\\\\\((.+?)\\\\\)$/s', $latex, $m)) $latex = trim($m[1]);
            elseif (preg_match('/^\\\\\[(.+?)\\\\\]$/s', $latex, $m)) $latex = trim($m[1]);

            return $this->latexToOmml($latex);
        }
        return '';
    }

    private function processOrderedList(DOMElement $olNode, array $runCtx): string
    {
        $result = '';
        $i = 1;
        foreach ($olNode->childNodes as $li) {
            if (!($li instanceof DOMElement) || strtolower($li->tagName) !== 'li') continue;
            $prefix = $this->makeRun($this->escXml("{$i}."), $runCtx) . '<w:r><w:tab/></w:r>';
            $body   = '';
            foreach ($li->childNodes as $child) $body .= $this->processNode($child, $runCtx);
            $result .= $this->makeParagraph($prefix . $body, null, '720', '360');
            $i++;
        }
        return $result;
    }

    private function processUnorderedList(DOMElement $ulNode, array $runCtx): string
    {
        $result = '';
        foreach ($ulNode->childNodes as $li) {
            if (!($li instanceof DOMElement) || strtolower($li->tagName) !== 'li') continue;
            $prefix = $this->makeRun($this->escXml("•"), $runCtx) . '<w:r><w:tab/></w:r>';
            $body   = '';
            foreach ($li->childNodes as $child) $body .= $this->processNode($child, $runCtx);
            $result .= $this->makeParagraph($prefix . $body, null, '720', '360');
        }
        return $result;
    }

    // ── Table elements ────────────────────────────────────────

    private function processTable(DOMElement $node, array $runCtx, array $parsedStyle): string
    {
        $rows = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['thead', 'tbody', 'tfoot'])) {
                    foreach ($child->childNodes as $row) {
                        if ($row instanceof DOMElement && strtolower($row->tagName) === 'tr') {
                            $rows .= $this->processTableRow($row, $runCtx);
                        }
                    }
                } elseif ($tag === 'tr') {
                    $rows .= $this->processTableRow($child, $runCtx);
                }
            }
        }
        
        if (empty($rows)) {
            return '';
        }

        $maxCols = 0;
        $trNodes = $node->getElementsByTagName('tr');
        foreach ($trNodes as $tr) {
            $cols = 0;
            foreach ($tr->childNodes as $c) {
                if ($c instanceof DOMElement && in_array(strtolower($c->tagName), ['td', 'th'])) {
                    $colspan = (int)$c->getAttribute('colspan');
                    $cols += $colspan > 1 ? $colspan : 1;
                }
            }
            if ($cols > $maxCols) {
                $maxCols = $cols;
            }
        }
        
        $tblGrid = '<w:tblGrid>';
        for ($i = 0; $i < $maxCols; $i++) {
            $tblGrid .= '<w:gridCol/>';
        }
        $tblGrid .= '</w:tblGrid>';

        return '<w:tbl>'
             . '<w:tblPr>'
             . '<w:tblStyle w:val="TableGrid"/>'
             . '<w:tblW w:w="0" w:type="auto"/>'
             . '<w:tblBorders>'
             . '<w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '<w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '<w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>'
             . '</w:tblBorders>'
             . '</w:tblPr>'
             . $tblGrid
             . $rows
             . '</w:tbl>';
    }

    private function processTableRow(DOMElement $node, array $runCtx): string
    {
        $cells = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['td', 'th'])) {
                $cells .= $this->processTableCell($child, $runCtx);
            }
        }
        if (empty($cells)) {
            return '';
        }
        return '<w:tr>' . $cells . '</w:tr>';
    }

    private function processTableCell(DOMElement $node, array $runCtx): string
    {
        $isTh = strtolower($node->tagName) === 'th';
        if ($isTh) {
            $runCtx['bold'] = true;
        }

        $content = '';
        $pendingRuns = '';

        foreach ($node->childNodes as $child) {
            $isBlock = ($child instanceof DOMElement)
                && in_array(strtolower($child->tagName), self::BLOCK_ELEMENTS, true);

            if ($isBlock) {
                if ($pendingRuns !== '') {
                    $content .= '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr>' . $pendingRuns . '</w:p>';
                    $pendingRuns = '';
                }
                $content .= $this->processNode($child, $runCtx);
            } else {
                $pendingRuns .= $this->processNode($child, $runCtx);
            }
        }

        if ($pendingRuns !== '') {
            $content .= '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr>' . $pendingRuns . '</w:p>';
        }

        if (empty($content)) {
            $content = '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr></w:p>';
        } else {
            if (!str_ends_with(trim($content), '</w:p>')) {
                $content .= '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr></w:p>';
            }
        }

        $tcPr = '<w:tcW w:w="0" w:type="auto"/>';
        
        $colspan = (int) $node->getAttribute('colspan');
        if ($colspan > 1) {
            $tcPr .= '<w:gridSpan w:val="' . $colspan . '"/>';
        }

        // Aligning content in cell vertically
        $tcPr .= '<w:vAlign w:val="center"/>';

        return '<w:tc><w:tcPr>' . $tcPr . '</w:tcPr>' . $content . '</w:tc>';
    }

    // ── XML builders ──────────────────────────────────────────
    private function makeRun(string $xmlText, array $ctx): string
    {
        $rPr = $this->buildRPr($ctx);
        return "<w:r>" . ($rPr ? "<w:rPr>{$rPr}</w:rPr>" : '') . "<w:t xml:space=\"preserve\">{$xmlText}</w:t></w:r>";
    }

    private function makeParagraph(string $runs, ?string $align, ?int $indLeft = null, ?int $indHanging = null): string
    {
        $pPr = '';
        if ($align) $pPr .= '<w:jc w:val="' . $this->mapAlign($align) . '"/>';
        if ($indLeft !== null) {
            $hang = $indHanging !== null ? " w:hanging=\"{$indHanging}\"" : '';
            $pPr .= "<w:ind w:left=\"{$indLeft}\"{$hang}/>";
        }
        return '<w:p>' . ($pPr ? "<w:pPr>{$pPr}</w:pPr>" : '') . $runs . '</w:p>';
    }

    private function buildRPr(array $ctx): string
    {
        $x = '';
        if (!empty($ctx['bold']))        $x .= '<w:b/><w:bCs/>';
        if (!empty($ctx['italic']))      $x .= '<w:i/><w:iCs/>';
        if (!empty($ctx['underline']))   $x .= '<w:u w:val="single"/>';
        if (!empty($ctx['strikethrough'])) $x .= '<w:strike/>';
        if (!empty($ctx['vertAlign']))   $x .= "<w:vertAlign w:val=\"{$ctx['vertAlign']}\"/>";
        if (!empty($ctx['color']))       $x .= "<w:color w:val=\"{$ctx['color']}\"/>";
        if (!empty($ctx['font-size'])) {
            $sz = $this->ptToHalfPt((string)$ctx['font-size']);
            if ($sz > 0) $x .= "<w:sz w:val=\"{$sz}\"/><w:szCs w:val=\"{$sz}\"/>";
        }
        if (!empty($ctx['background-color'])) {
            $hex = strtoupper($this->cssColor((string)$ctx['background-color']));
            $hl = self::HIGHLIGHT_MAP[$hex] ?? null;
            if ($hl) {
                // Use w:highlight for standard Word highlight colors
                $x .= "<w:highlight w:val=\"{$hl}\"/>";
            } else {
                // Use w:shd (shading) for arbitrary background colors — always works
                $x .= "<w:shd w:val=\"clear\" w:color=\"auto\" w:fill=\"{$hex}\"/>";
            }
        }
        if (!empty($ctx['font-family'])) {
            $ff = $this->escXml(explode(',', $ctx['font-family'])[0]);
            $x .= "<w:rFonts w:ascii=\"{$ff}\" w:hAnsi=\"{$ff}\"/>";
        }
        return $x;
    }

    // ── Style parsing helpers ─────────────────────────────────
    private function styleToCtx(array $parsed): array
    {
        $ctx = [];
        if (isset($parsed['color']))            $ctx['color'] = $this->cssColor($parsed['color']);
        if (isset($parsed['font-size']))        $ctx['font-size'] = $parsed['font-size'];
        if (isset($parsed['background-color'])) $ctx['background-color'] = $parsed['background-color'];
        if (isset($parsed['font-family']))      $ctx['font-family'] = trim($parsed['font-family'], '"\'');
        if (($parsed['font-weight'] ?? '') === 'bold') $ctx['bold'] = true;
        if (($parsed['font-style'] ?? '') === 'italic') $ctx['italic'] = true;
        if (isset($parsed['text-decoration'])) {
            if (str_contains($parsed['text-decoration'], 'underline'))    $ctx['underline'] = true;
            if (str_contains($parsed['text-decoration'], 'line-through')) $ctx['strikethrough'] = true;
        }
        return $ctx;
    }

    private function parseStyle(string $style): array
    {
        $result = [];
        foreach (explode(';', $style) as $dec) {
            $parts = explode(':', $dec, 2);
            if (count($parts) === 2) {
                $result[trim(strtolower($parts[0]))] = trim($parts[1]);
            }
        }
        return $result;
    }

    private function cssColor(string $color): string
    {
        $color = trim(strtolower($color));
        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $m)) return strtoupper($m[1]);
        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $m)) {
            return strtoupper($m[1][0].$m[1][0].$m[1][1].$m[1][1].$m[1][2].$m[1][2]);
        }
        if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $color, $m)) {
            return strtoupper(sprintf('%02X%02X%02X', $m[1], $m[2], $m[3]));
        }
        $named = [
            'red' => 'FF0000', 'blue' => '0000FF', 'green' => '008000', 'yellow' => 'FFFF00',
            'black' => '000000', 'white' => 'FFFFFF', 'orange' => 'FFA500', 'purple' => '800080',
            'pink' => 'FFC0CB', 'gray' => '808080', 'grey' => '808080',
            'brown' => 'A52A2A', 'navy' => '000080', 'teal' => '008080',
            'maroon' => '800000', 'olive' => '808000', 'lime' => '00FF00',
            'aqua' => '00FFFF', 'fuchsia' => 'FF00FF', 'silver' => 'C0C0C0',
        ];
        return $named[$color] ?? '000000';
    }

    private function mapHighlight(string $bgColor): string
    {
        $hex = strtoupper($this->cssColor($bgColor));
        return self::HIGHLIGHT_MAP[$hex] ?? 'none';
    }

    private function mapAlign(string $align): string
    {
        return match (trim(strtolower($align))) {
            'center'  => 'center',
            'right'   => 'right',
            'justify' => 'both',
            default   => 'left',
        };
    }

    private function ptToHalfPt(string $size): int
    {
        $val = (float) $size;
        if (str_contains(strtolower($size), 'px')) $val *= 0.75;
        return (int)($val * 2);
    }

    private function cssLengthToTwips(string $len): ?int
    {
        if (str_ends_with($len, 'twips')) return (int) $len;  // already twips marker
        $val = (float) $len;
        if (str_contains(strtolower($len), 'px')) return (int)($val * 15);   // 1px ≈ 15 twips
        if (str_contains(strtolower($len), 'pt')) return (int)($val * 20);   // 1pt = 20 twips
        return (int)($val * 15); // default assume px
    }

    private function escXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // ══════════════════════════════════════════════════════════
    // LaTeX → OMML (Office Math Markup Language) Converter
    // Converts \(...\) delimited LaTeX in OOXML text runs to
    // native Word math equations using <m:oMath> elements.
    // ══════════════════════════════════════════════════════════

    private function convertLatexToOmml(string $ooxml): string
    {
        // Legacy convertLatexToOmml: no longer needed since we handle it in processNode.
        // We keep this method just to return the original string in case it's called.
        return $ooxml;
    }

    /**
     * Convert a LaTeX math expression to OMML (Office Math Markup Language).
     * Supports: \frac{a}{b}, \times, \pm, \sqrt{x}, superscript ^, subscript _,
     *           Greek letters, common operators, and nested expressions.
     */
    private function latexToOmml(string $latex): string
    {
        $inner = $this->parseLatexExpr($latex);
        return '<m:oMath xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math">'
             . $inner
             . '</m:oMath>';
    }

    /**
     * Parse a full LaTeX expression into OMML elements, handling tokens left-to-right.
     */
    private function parseLatexExpr(string $expr): string
    {
        $result = '';
        $i = 0;
        $len = strlen($expr);

        while ($i < $len) {
            // Skip whitespace
            if (ctype_space($expr[$i])) { $i++; continue; }

            // Backslash command
            if ($expr[$i] === '\\') {
                $cmd = $this->readCommand($expr, $i);

                if ($cmd === 'frac') {
                    $num = $this->readGroup($expr, $i);
                    $den = $this->readGroup($expr, $i);
                    $result .= '<m:f><m:fPr><m:type m:val="bar"/></m:fPr>'
                             . '<m:num>' . $this->parseLatexExpr($num) . '</m:num>'
                             . '<m:den>' . $this->parseLatexExpr($den) . '</m:den>'
                             . '</m:f>';
                } elseif ($cmd === 'sqrt') {
                    $content = $this->readGroup($expr, $i);
                    $result .= '<m:rad><m:radPr><m:degHide m:val="1"/></m:radPr>'
                             . '<m:deg/>'
                             . '<m:e>' . $this->parseLatexExpr($content) . '</m:e>'
                             . '</m:rad>';
                } elseif ($cmd === 'sum') {
                    $result .= $this->makeOmmlRun('∑');
                } elseif ($cmd === 'prod') {
                    $result .= $this->makeOmmlRun('∏');
                } elseif ($cmd === 'int') {
                    $result .= $this->makeOmmlRun('∫');
                } elseif ($cmd === 'infty') {
                    $result .= $this->makeOmmlRun('∞');
                } elseif ($cmd === 'leq' || $cmd === 'le') {
                    $result .= $this->makeOmmlRun('≤');
                } elseif ($cmd === 'geq' || $cmd === 'ge') {
                    $result .= $this->makeOmmlRun('≥');
                } elseif ($cmd === 'neq' || $cmd === 'ne') {
                    $result .= $this->makeOmmlRun('≠');
                } elseif ($cmd === 'times') {
                    $result .= $this->makeOmmlRun('×');
                } elseif ($cmd === 'div') {
                    $result .= $this->makeOmmlRun('÷');
                } elseif ($cmd === 'pm') {
                    $result .= $this->makeOmmlRun('±');
                } elseif ($cmd === 'cdot') {
                    $result .= $this->makeOmmlRun('·');
                } elseif ($cmd === 'ldots' || $cmd === 'dots') {
                    $result .= $this->makeOmmlRun('…');
                } elseif ($cmd === 'left' || $cmd === 'right') {
                    // Skip \left and \right, just output the next bracket char
                    if ($i < $len && in_array($expr[$i], ['(', ')', '[', ']', '{', '}', '|'])) {
                        $result .= $this->makeOmmlRun($expr[$i]);
                        $i++;
                    }
                } elseif ($cmd === 'text') {
                    $textContent = $this->readGroup($expr, $i);
                    $result .= '<m:r><m:rPr><m:nor/></m:rPr><m:t>' . $this->escXml($textContent) . '</m:t></m:r>';
                } elseif (($greek = $this->greekLetter($cmd)) !== null) {
                    $result .= $this->makeOmmlRun($greek);
                } else {
                    // Unknown command — output as text
                    $result .= $this->makeOmmlRun('\\' . $cmd);
                }
                continue;
            }

            // Superscript
            if ($expr[$i] === '^') {
                $i++;
                $sup = $this->readGroupOrChar($expr, $i);
                // Pop last element for base — simplified: wrap previous run
                $result .= '<m:sSup><m:e>' . $this->makeOmmlRun('') . '</m:e>'
                         . '<m:sup>' . $this->parseLatexExpr($sup) . '</m:sup></m:sSup>';
                continue;
            }

            // Subscript
            if ($expr[$i] === '_') {
                $i++;
                $sub = $this->readGroupOrChar($expr, $i);
                $result .= '<m:sSub><m:e>' . $this->makeOmmlRun('') . '</m:e>'
                         . '<m:sub>' . $this->parseLatexExpr($sub) . '</m:sub></m:sSub>';
                continue;
            }

            // Group {…}
            if ($expr[$i] === '{') {
                $content = $this->readGroup($expr, $i);
                $result .= $this->parseLatexExpr($content);
                continue;
            }

            // Regular character (numbers, letters, operators)
            $result .= $this->makeOmmlRun($expr[$i]);
            $i++;
        }

        return $result;
    }

    /** Read a \command, advancing $i past it */
    private function readCommand(string $expr, int &$i): string
    {
        $i++; // skip backslash
        $start = $i;
        $len = strlen($expr);
        while ($i < $len && ctype_alpha($expr[$i])) $i++;
        return substr($expr, $start, $i - $start);
    }

    /** Read a {group}, advancing $i past }. Returns inner content. */
    private function readGroup(string $expr, int &$i): string
    {
        $len = strlen($expr);
        // Skip whitespace
        while ($i < $len && ctype_space($expr[$i])) $i++;
        if ($i >= $len || $expr[$i] !== '{') return '';
        $i++; // skip {
        $depth = 1;
        $start = $i;
        while ($i < $len && $depth > 0) {
            if ($expr[$i] === '{') $depth++;
            elseif ($expr[$i] === '}') $depth--;
            if ($depth > 0) $i++;
        }
        $content = substr($expr, $start, $i - $start);
        if ($i < $len) $i++; // skip }
        return $content;
    }

    /** Read a {group} or single character for ^/_ */
    private function readGroupOrChar(string $expr, int &$i): string
    {
        $len = strlen($expr);
        while ($i < $len && ctype_space($expr[$i])) $i++;
        if ($i >= $len) return '';
        if ($expr[$i] === '{') return $this->readGroup($expr, $i);
        $ch = $expr[$i];
        $i++;
        return $ch;
    }

    /** Create an OMML math run <m:r><m:t>text</m:t></m:r> */
    private function makeOmmlRun(string $text): string
    {
        if ($text === '') return '';
        return '<m:r><m:t>' . $this->escXml($text) . '</m:t></m:r>';
    }

    /** Map LaTeX Greek letter commands to Unicode characters */
    private function greekLetter(string $cmd): ?string
    {
        $map = [
            'alpha' => 'α', 'beta' => 'β', 'gamma' => 'γ', 'delta' => 'δ',
            'epsilon' => 'ε', 'zeta' => 'ζ', 'eta' => 'η', 'theta' => 'θ',
            'iota' => 'ι', 'kappa' => 'κ', 'lambda' => 'λ', 'mu' => 'μ',
            'nu' => 'ν', 'xi' => 'ξ', 'pi' => 'π', 'rho' => 'ρ',
            'sigma' => 'σ', 'tau' => 'τ', 'upsilon' => 'υ', 'phi' => 'φ',
            'chi' => 'χ', 'psi' => 'ψ', 'omega' => 'ω',
            'Alpha' => 'Α', 'Beta' => 'Β', 'Gamma' => 'Γ', 'Delta' => 'Δ',
            'Epsilon' => 'Ε', 'Zeta' => 'Ζ', 'Eta' => 'Η', 'Theta' => 'Θ',
            'Lambda' => 'Λ', 'Xi' => 'Ξ', 'Pi' => 'Π', 'Sigma' => 'Σ',
            'Phi' => 'Φ', 'Psi' => 'Ψ', 'Omega' => 'Ω',
        ];
        return $map[$cmd] ?? null;
    }
}

