<?php
namespace App\Helpers;

use App\Core\Database;

/**
 * Branded PDF report writer without external dependencies.
 * Layout is optimized for readable multi-column landscape reports.
 */
class PdfHelper
{
    private static function sanitize(string $text): string
    {
        $text = str_replace(["\r", "\n"], ' ', $text);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }

    private static function getSetting(string $key, string $default = ''): string
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $value = $stmt->fetchColumn();
            return $value !== false ? (string)$value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function tableReport(string $title, array $headers, array $rows, array $metadata = [], array $columns = []): string
    {
        $generatedAt = date('d F Y H:i');
        $columnCount = count($headers);
        $landscape = $columnCount >= 6;
        $pageW = $landscape ? 842 : 595;
        $pageH = $landscape ? 595 : 842;
        $margin = 40;
        $contentW = $pageW - ($margin * 2);

        $colWidths = self::calculateColumnWidths($headers, $columns, $contentW);

        $appName = self::getSetting('app_short_name', 'BIG');
        $companyName = self::getSetting('company_name', 'PT. BIG');
        $appSystem = 'Sistem Gudang';

        $pages = [];
        $current = [];
        $topY = $pageH - $margin;
        $y = $topY;
        $fontSize = 8.5;
        $lineH = 10;
        $cellPad = 4;
        $footerReserve = 48;

        $flushPage = function () use (&$pages, &$current): void {
            if (!empty($current)) {
                $pages[] = $current;
                $current = [];
            }
        };

        $drawBranding = function () use (&$current, &$y, $margin, $pageW, $appName, $appSystem, $companyName): void {
            $current[] = "0.1 0.4 0.8 rg {$margin} " . ($y - 34) . " 34 34 re f";
            $current[] = 'BT /F2 12 Tf 1 1 1 rg ' . ($margin + 6) . ' ' . ($y - 22) . ' Td (' . self::sanitize($appName) . ') Tj ET';
            $current[] = 'BT /F2 16 Tf 0 0 0 rg ' . ($margin + 46) . ' ' . ($y - 11) . ' Td (' . self::sanitize($appName) . ') Tj ET';
            $current[] = 'BT /F1 10 Tf 0.4 0.4 0.4 rg ' . ($margin + 46) . ' ' . ($y - 25) . ' Td (' . self::sanitize($appSystem) . ') Tj ET';

            $fs = 14;
            if (strlen($companyName) > 28) $fs = 11;
            elseif (strlen($companyName) > 22) $fs = 12;
            $estimated = strlen($companyName) * $fs * 0.58;
            $companyX = max($margin + 250, $pageW - $margin - $estimated);
            $current[] = 'BT /F2 ' . $fs . ' Tf 0 0 0 rg ' . $companyX . ' ' . ($y - 16) . ' Td (' . self::sanitize($companyName) . ') Tj ET';
            $current[] = "0.8 0.8 0.8 RG 0.5 w {$margin} " . ($y - 42) . ' ' . ($pageW - $margin) . ' ' . ($y - 42) . ' S';
            $y -= 55;
        };

        $drawReportHeader = function () use (&$current, &$y, $margin, $title, $metadata, $contentW): void {
            $y -= 5;
            $current[] = 'BT /F2 14 Tf 0 0 0 rg ' . $margin . ' ' . $y . ' Td (' . self::sanitize(mb_strtoupper($title)) . ') Tj ET';
            $y -= 15;

            $leftX = $margin;
            $rightX = $margin + ($contentW / 2);
            $index = 0;
            foreach ($metadata as $key => $value) {
                $x = ($index % 2 === 0) ? $leftX : $rightX;
                $current[] = 'BT /F1 8.5 Tf 0.3 0.3 0.3 rg ' . $x . ' ' . $y . ' Td (' . self::sanitize((string)$key) . ' : ) Tj ET';
                $current[] = 'BT /F2 8.5 Tf 0 0 0 rg ' . ($x + 66) . ' ' . $y . ' Td (' . self::sanitize((string)$value) . ') Tj ET';
                if ($index % 2 === 1) $y -= 11;
                $index++;
            }
            if ($index % 2 === 1) $y -= 11;
            $y -= 7;
        };

        $drawTableHeader = function () use (&$current, &$y, $headers, $colWidths, $margin, $contentW, $lineH, $cellPad): void {
            $headerLines = [];
            $maxLines = 1;
            foreach ($headers as $i => $header) {
                $headerLines[$i] = self::wrapText((string)$header, max(24, $colWidths[$i] - 8), 8.5);
                $maxLines = max($maxLines, count($headerLines[$i]));
            }
            $headerH = ($maxLines * $lineH) + ($cellPad * 2);
            $current[] = "0.16 0.25 0.38 rg {$margin} " . ($y - $headerH) . " {$contentW} {$headerH} re f";

            $x = $margin;
            foreach ($headers as $i => $header) {
                $ty = $y - $cellPad - 8;
                foreach ($headerLines[$i] as $line) {
                    $current[] = 'BT /F2 8.5 Tf 1 1 1 rg ' . ($x + 4) . ' ' . $ty . ' Td (' . self::sanitize($line) . ') Tj ET';
                    $ty -= $lineH;
                }
                $x += $colWidths[$i];
            }
            $y -= $headerH;
        };

        $drawBranding();
        $drawReportHeader();

        if (empty($rows)) {
            $y -= 20;
            $current[] = 'BT /F1 10 Tf 0.5 0.5 0.5 rg ' . $margin . ' ' . $y . ' Td (Tidak ada data untuk kriteria yang dipilih.) Tj ET';
        } else {
            $drawTableHeader();
            $rowIndex = 0;
            foreach ($rows as $row) {
                $cells = [];
                $maxLines = 1;
                foreach ($headers as $i => $header) {
                    $column = $columns[$i] ?? $header;
                    $raw = is_array($row) ? ($row[$column] ?? '') : '';
                    $formatted = self::formatCell((string)$column, $raw);
                    $cells[$i] = self::wrapText((string)$formatted, max(24, $colWidths[$i] - 8), $fontSize);
                    $maxLines = max($maxLines, count($cells[$i]));
                }

                $rowH = max(20, ($maxLines * $lineH) + ($cellPad * 2));
                if ($y - $rowH < $margin + $footerReserve) {
                    $flushPage();
                    $y = $topY;
                    $drawBranding();
                    $drawTableHeader();
                }

                if ($rowIndex % 2 === 0) {
                    $current[] = "0.95 0.96 0.98 rg {$margin} " . ($y - $rowH) . " {$contentW} {$rowH} re f";
                }

                $x = $margin;
                foreach ($headers as $i => $header) {
                    $ty = $y - $cellPad - 8;
                    foreach ($cells[$i] as $line) {
                        $current[] = 'BT /F1 ' . $fontSize . ' Tf 0.08 0.08 0.08 rg ' . ($x + 4) . ' ' . $ty . ' Td (' . self::sanitize($line) . ') Tj ET';
                        $ty -= $lineH;
                    }
                    $x += $colWidths[$i];
                }

                $current[] = "0.84 0.86 0.9 RG 0.45 w {$margin} " . ($y - $rowH) . ' ' . ($margin + $contentW) . ' ' . ($y - $rowH) . ' S';
                $y -= $rowH;
                $rowIndex++;
            }
        }

        $flushPage();

        $totalPages = count($pages);
        $contentStreams = [];
        $pageObjects = [];
        $kids = [];
        $objId = 3;
        $pageNumber = 1;

        foreach ($pages as $pageLines) {
            $pageId = $objId;
            $contentId = $objId + 1;
            $objId += 2;
            $kids[] = "$pageId 0 R";

            $footerLeft = "$companyName — $appSystem";
            $footerRight = "Dicetak: $generatedAt | Hal. $pageNumber dari $totalPages";
            $footerRightW = strlen($footerRight) * 4.2;
            $footer = "0.8 0.8 0.8 RG 0.5 w {$margin} 35 " . ($pageW - $margin) . " 35 S\n";
            $footer .= 'BT /F1 8 Tf 0.5 0.5 0.5 rg ' . $margin . ' 22 Td (' . self::sanitize($footerLeft) . ") Tj ET\n";
            $footer .= 'BT /F1 8 Tf 0.5 0.5 0.5 rg ' . ($pageW - $margin - $footerRightW) . ' 22 Td (' . self::sanitize($footerRight) . ') Tj ET';

            $body = implode("\n", $pageLines) . "\n" . $footer;
            $contentStreams[$contentId] = $body;
            $pageObjects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageW} {$pageH}] /Resources << /Font << /F1 " . ($objId + 1) . " 0 R /F2 {$objId} 0 R >> >> /Contents {$contentId} 0 R >>";
            $pageNumber++;
        }

        $fontBoldId = $objId;
        $fontRegId = $objId + 1;

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($pages) . ' >>',
        ];
        foreach ($pageObjects as $id => $spec) $objects[$id] = $spec;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[$fontRegId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        foreach ($contentStreams as $id => $body) $objects[$id] = $body;

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n";
            if (isset($contentStreams[$id])) {
                $pdf .= '<< /Length ' . strlen($body) . ">>\nstream\n{$body}\nendstream\n";
            } else {
                $pdf .= $body . "\n";
            }
            $pdf .= "endobj\n";
        }

        $xrefPos = strlen($pdf);
        $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= isset($offsets[$id])
                ? sprintf("%010d 00000 n \n", $offsets[$id])
                : "0000000000 00000 f \n";
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }

    private static function calculateColumnWidths(array $headers, array $columns, float $contentW): array
    {
        $count = count($headers);
        if ($count === 0) return [];

        $weights = [];
        $minimums = [];
        $fixed = 0.0;
        $flexTotal = 0.0;

        foreach ($headers as $i => $header) {
            $h = strtolower(trim((string)$header));
            $col = strtolower((string)($columns[$i] ?? $header));

            if ($h === 'no') {
                $minimums[$i] = 28; $weights[$i] = 0; $fixed += 28; continue;
            }
            if ($h === 'tanggal' || str_contains($h, 'tanggal')) {
                $minimums[$i] = 68; $weights[$i] = 10; $flexTotal += 10; continue;
            }
            if (str_contains($h, 'kode') || str_contains($col, 'kode')) {
                $minimums[$i] = 82; $weights[$i] = 12; $flexTotal += 12; continue;
            }
            if (str_contains($h, 'nama barang') || str_contains($col, 'item_name')) {
                $minimums[$i] = 105; $weights[$i] = 18; $flexTotal += 18; continue;
            }
            if (str_contains($h, 'gudang') || str_contains($col, 'warehouse')) {
                $minimums[$i] = 115; $weights[$i] = 20; $flexTotal += 20; continue;
            }
            if (str_contains($h, 'tipe mutasi') || str_contains($col, 'transaction_type')) {
                $minimums[$i] = 88; $weights[$i] = 14; $flexTotal += 14; continue;
            }
            if (str_contains($h, 'jumlah') || str_contains($h, 'stok')) {
                $minimums[$i] = 62; $weights[$i] = 8; $flexTotal += 8; continue;
            }
            if (str_contains($h, 'diproses') || str_contains($h, 'oleh') || str_contains($h, 'pengirim') || str_contains($h, 'penerima')) {
                $minimums[$i] = 82; $weights[$i] = 12; $flexTotal += 12; continue;
            }

            $minimums[$i] = 70;
            $weights[$i] = 10;
            $flexTotal += 10;
        }

        $remaining = $contentW - $fixed;
        $sumMinimum = array_sum($minimums);
        if ($sumMinimum > $contentW) {
            $scale = $remaining / max(1, $sumMinimum - $fixed);
            foreach ($minimums as $i => $v) {
                if (($weights[$i] ?? 0) === 0) continue;
                $minimums[$i] = max(40, $v * $scale);
            }
            $sumMinimum = array_sum($minimums);
            $remaining = $contentW - $fixed;
        }

        $extra = max(0, $remaining - ($sumMinimum - $fixed));
        $widths = [];
        foreach ($headers as $i => $_) {
            if (($weights[$i] ?? 0) === 0) {
                $widths[$i] = $minimums[$i];
            } else {
                $widths[$i] = $minimums[$i] + ($extra * ($weights[$i] / max(1, $flexTotal)));
            }
        }

        $total = array_sum($widths);
        if ($total > 0 && abs($total - $contentW) > 0.5) {
            $scale = $contentW / $total;
            foreach ($widths as $i => $w) $widths[$i] = max(36, $w * $scale);
        }
        return $widths;
    }

    private static function wrapText(string $text, float $width, float $fontSize): array
    {
        $text = trim($text);
        if ($text === '') return [''];

        // Conservative character estimate so PDF text never visually runs into the next column.
        $maxChars = max(1, (int)floor($width / max(1, $fontSize * 0.66)));
        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if ($word === '') continue;
            while (mb_strlen($word) > $maxChars) {
                if ($line !== '') { $lines[] = $line; $line = ''; }
                $lines[] = mb_substr($word, 0, $maxChars);
                $word = mb_substr($word, $maxChars);
            }
            if ($word === '') continue;

            $candidate = $line === '' ? $word : ($line . ' ' . $word);
            if (mb_strlen($candidate) <= $maxChars) {
                $line = $candidate;
            } else {
                if ($line !== '') $lines[] = $line;
                $line = $word;
            }
        }
        if ($line !== '') $lines[] = $line;
        return $lines ?: [''];
    }

    private static function formatCell(string $column, $value): string
    {
        if ($value === null || $value === '') return '';
        if (in_array($column, ['created_at','sent_at','received_at','installed_at','loan_date_display','updated_at','reported_date','tanggal_mutasi'], true)) {
            $ts = strtotime((string)$value);
            if ($ts !== false) return date('Y-m-d H:i', $ts);
        }
        if (in_array($column, ['status','condition_status','type','transaction_type','action'], true)) {
            return \App\Helpers\UIHelper::statusLabel((string)$value);
        }
        return (string)$value;
    }
}
