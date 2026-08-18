<?php
namespace App\Helpers;

use App\Core\Database;

class PdfHelper {
    private static function sanitize(string $text): string {
        $text = str_replace(["\r", "\n"], ' ', $text);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted === false) $converted = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }

    private static function getSetting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (\Throwable $e) { return $default; }
    }

    public static function tableReport(string $title, array $headers, array $rows, array $metadata = [], array $columns = []): string {
        $generatedAt = date('d F Y H:i');
        $cols = count($headers);
        $landscape = $cols > 6;
        $pageW = $landscape ? 842 : 595;
        $pageH = $landscape ? 595 : 842;
        $margin = 40;
        $contentW = $pageW - 2 * $margin;
        $colWidths = self::calculateColumnWidths($headers, $columns, $rows, $contentW);

        $appName = self::getSetting('app_short_name', 'BIG');
        $companyName = self::getSetting('company_name', 'PT. BIG');
        $appSystem = 'Sistem Gudang';
        $faviconPath = self::getSetting('favicon_path');
        $logoJpg = '';
        $logoW = 0;
        $logoH = 0;
        if ($faviconPath) {
            $path = __DIR__ . '/../../storage/private_uploads/' . $faviconPath;
            if (file_exists($path)) {
                $info = @getimagesize($path);
                if ($info && ($info['mime'] ?? '') === 'image/jpeg') {
                    $logoJpg = (string)@file_get_contents($path);
                    $logoW = (int)$info[0];
                    $logoH = (int)$info[1];
                }
            }
        }

        $pages = [];
        $current = [];
        $top = $pageH - $margin;
        $y = $top;
        $lineH = 10;
        $cellPad = 4;
        $footerReserve = 48;

        $flush = function() use (&$pages, &$current): void {
            if ($current) { $pages[] = $current; $current = []; }
        };

        $branding = function() use (&$current, &$y, $pageW, $margin, $appName, $appSystem, $companyName, $logoJpg, $logoW, $logoH): void {
            if ($logoJpg && $logoW && $logoH) {
                $ratio = min(40 / $logoW, 40 / $logoH);
                $dw = $logoW * $ratio; $dh = $logoH * $ratio;
                $oy = (40 - $dh) / 2;
                $current[] = 'q ' . number_format($dw,2,'.','') . ' 0 0 ' . number_format($dh,2,'.','') . ' ' . $margin . ' ' . ($y - 40 + $oy) . ' cm /Im1 Do Q';
            } else {
                $current[] = "0.1 0.4 0.8 rg {$margin} " . ($y - 35) . ' 35 35 re f';
                $current[] = 'BT /F2 12 Tf 1 1 1 rg ' . ($margin + 6) . ' ' . ($y - 23) . ' Td (' . self::sanitize($appName) . ') Tj ET';
            }
            $current[] = 'BT /F2 16 Tf 0 0 0 rg ' . ($margin + 50) . ' ' . ($y - 12) . ' Td (' . self::sanitize($appName) . ') Tj ET';
            $current[] = 'BT /F1 10 Tf 0.4 0.4 0.4 rg ' . ($margin + 50) . ' ' . ($y - 26) . ' Td (' . self::sanitize($appSystem) . ') Tj ET';
            $fs = strlen($companyName) > 30 ? 10 : (strlen($companyName) > 22 ? 12 : 14);
            $cw = strlen($companyName) * ($fs * 0.65);
            $current[] = 'BT /F2 ' . $fs . ' Tf 0 0 0 rg ' . ($pageW - $margin - $cw) . ' ' . ($y - 18) . ' Td (' . self::sanitize($companyName) . ') Tj ET';
            $current[] = "0.8 0.8 0.8 RG 0.5 w {$margin} " . ($y - 42) . ' ' . ($pageW - $margin) . ' ' . ($y - 42) . ' S';
            $y -= 55;
        };

        $reportHeader = function() use (&$current, &$y, $margin, $title, $metadata, $contentW): void {
            $y -= 8;
            $current[] = 'BT /F2 14 Tf 0 0 0 rg ' . $margin . ' ' . $y . ' Td (' . self::sanitize(mb_strtoupper($title)) . ') Tj ET';
            $y -= 16;
            $left = $margin; $right = $margin + ($contentW / 2); $i = 0;
            foreach ($metadata as $k => $v) {
                $x = ($i % 2 === 0) ? $left : $right;
                $current[] = 'BT /F1 9 Tf 0.3 0.3 0.3 rg ' . $x . ' ' . $y . ' Td (' . self::sanitize((string)$k) . ' : ) Tj ET';
                $current[] = 'BT /F2 9 Tf 0 0 0 rg ' . ($x + 70) . ' ' . $y . ' Td (' . self::sanitize((string)$v) . ') Tj ET';
                if ($i % 2 === 1) $y -= 11;
                $i++;
            }
            if ($i % 2 === 1) $y -= 11;
            $y -= 6;
        };

        $tableHeader = function() use (&$current, &$y, $headers, $colWidths, $margin, $contentW): void {
            $headerLine = 10; $pad = 5; $wrapped = []; $max = 1;
            foreach ($headers as $i => $h) {
                $wrapped[$i] = self::wrapText((string)$h, max(20, $colWidths[$i] - 8), 8.5);
                $max = max($max, count($wrapped[$i]));
            }
            $h = ($max * $headerLine) + ($pad * 2);
            $current[] = "0.2 0.3 0.4 rg {$margin} " . ($y - $h) . " {$contentW} {$h} re f";
            $x = $margin;
            foreach ($headers as $i => $header) {
                $ty = $y - $pad - 8;
                foreach ($wrapped[$i] as $line) {
                    $current[] = 'BT /F2 8.5 Tf 1 1 1 rg ' . ($x + 4) . ' ' . $ty . ' Td (' . self::sanitize($line) . ') Tj ET';
                    $ty -= $headerLine;
                }
                $x += $colWidths[$i];
            }
            $y -= $h;
        };

        $branding();
        $reportHeader();

        if (!$rows) {
            $y -= 20;
            $current[] = 'BT /F1 10 Tf 0.5 0.5 0.5 rg ' . $margin . ' ' . $y . ' Td (Tidak ada data untuk kriteria yang dipilih.) Tj ET';
        } else {
            $tableHeader();
            $rowIndex = 0;
            foreach ($rows as $row) {
                $cells = []; $maxLines = 1;
                foreach ($headers as $i => $header) {
                    $col = $columns[$i] ?? $header;
                    $val = self::formatCell((string)$col, is_array($row) ? ($row[$col] ?? '') : '');
                    $cells[$i] = self::wrapText((string)$val, max(20, $colWidths[$i] - 8), 8);
                    $maxLines = max($maxLines, count($cells[$i]));
                }
                $rowH = max(18, ($maxLines * $lineH) + ($cellPad * 2));
                if ($y - $rowH < $margin + $footerReserve) {
                    $flush(); $y = $top; $branding(); $tableHeader();
                }
                if ($rowIndex % 2 === 0) {
                    $current[] = "0.95 0.96 0.98 rg {$margin} " . ($y - $rowH) . " {$contentW} {$rowH} re f";
                }
                $x = $margin;
                foreach ($headers as $i => $header) {
                    $ty = $y - $cellPad - 8;
                    foreach ($cells[$i] as $line) {
                        $current[] = 'BT /F1 8 Tf 0.1 0.1 0.1 rg ' . ($x + 4) . ' ' . $ty . ' Td (' . self::sanitize($line) . ') Tj ET';
                        $ty -= $lineH;
                    }
                    $x += $colWidths[$i];
                }
                $current[] = "0.85 0.87 0.90 RG 0.5 w {$margin} " . ($y - $rowH) . ' ' . ($margin + $contentW) . ' ' . ($y - $rowH) . ' S';
                $y -= $rowH; $rowIndex++;
            }
        }
        $flush();

        $totalPages = count($pages);
        $contentStreams = []; $pageObjects = []; $kids = []; $objId = 3; $pageNo = 1;
        $imageId = 0;
        if ($logoJpg) $imageId = $objId++;

        foreach ($pages as $lines) {
            $pageId = $objId; $contentId = $objId + 1; $objId += 2; $kids[] = "$pageId 0 R";
            $footerText = "$companyName — $appSystem";
            $footerRight = "Dicetak: $generatedAt | Hal. $pageNo dari $totalPages";
            $rightLen = strlen($footerRight) * 4.4;
            $footer = "0.8 0.8 0.8 RG 0.5 w {$margin} 35 " . ($pageW - $margin) . " 35 S\n";
            $footer .= 'BT /F1 8 Tf 0.5 0.5 0.5 rg ' . $margin . ' 22 Td (' . self::sanitize($footerText) . ") Tj ET\n";
            $footer .= 'BT /F1 8 Tf 0.5 0.5 0.5 rg ' . ($pageW - $margin - $rightLen) . ' 22 Td (' . self::sanitize($footerRight) . ') Tj ET';
            $body = implode("\n", $lines) . "\n" . $footer;
            $contentStreams[$contentId] = $body;
            $xobj = $logoJpg ? " /XObject << /Im1 {$imageId} 0 R >> " : '';
            $pageObjects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageW} {$pageH}] /Resources << /Font << /F1 " . ($objId + 1) . " 0 R /F2 {$objId} 0 R >> {$xobj} >> /Contents {$contentId} 0 R >>";
            $pageNo++;
        }

        $fontBoldId = $objId; $fontRegId = $objId + 1;
        $objects = [1 => "<< /Type /Catalog /Pages 2 0 R >>", 2 => "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($pages) . " >>"];
        if ($logoJpg) $objects[$imageId] = "<< /Type /XObject /Subtype /Image /Width {$logoW} /Height {$logoH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($logoJpg) . " >>\nstream\n{$logoJpg}\nendstream";
        foreach ($pageObjects as $id => $spec) $objects[$id] = $spec;
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[$fontRegId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        foreach ($contentStreams as $id => $body) $objects[$id] = $body;

        $pdf = "%PDF-1.4\n"; $offsets = []; ksort($objects);
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf); $pdf .= "$id 0 obj\n";
            if (isset($contentStreams[$id])) $pdf .= '<< /Length ' . strlen($body) . ">>\nstream\n{$body}\nendstream\n";
            else $pdf .= $body . "\n";
            $pdf .= "endobj\n";
        }
        $xrefPos = strlen($pdf); $maxId = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) $pdf .= isset($offsets[$id]) ? sprintf("%010d 00000 n \n", $offsets[$id]) : "0000000000 00000 f \n";
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }

    private static function calculateColumnWidths(array $headers, array $columns, array $rows, float $contentW): array {
        $count = count($headers); if (!$count) return [];
        $widths = array_fill(0, $count, 0.0); $weights = array_fill(0, $count, 1.0);
        $fixedNo = 30.0; $noCount = 0; $weightTotal = 0.0;
        foreach ($headers as $i => $header) {
            if (strtolower(trim((string)$header)) === 'no') { $widths[$i] = $fixedNo; $noCount++; continue; }
            $maxLen = mb_strlen((string)$header);
            foreach (array_slice($rows, 0, 60) as $row) {
                $col = $columns[$i] ?? $header;
                $val = is_array($row) ? self::formatCell((string)$col, $row[$col] ?? '') : '';
                $maxLen = max($maxLen, min(26, mb_strlen((string)$val)));
            }
            $n = strtolower((string)$header);
            if (str_contains($n, 'nama barang')) $maxLen = max($maxLen, 16);
            if (str_contains($n, 'gudang')) $maxLen = max($maxLen, 18);
            if (str_contains($n, 'tanggal') || str_contains($n, 'date')) $maxLen = min(max($maxLen, 11), 16);
            $weights[$i] = max(9, min(24, $maxLen)); $weightTotal += $weights[$i];
        }
        $remaining = $contentW - ($fixedNo * $noCount);
        if ($remaining <= 0 || $weightTotal <= 0) return array_fill(0, $count, $contentW / $count);
        for ($i = 0; $i < $count; $i++) if ($widths[$i] <= 0) $widths[$i] = $remaining * $weights[$i] / $weightTotal;
        $sum = array_sum($widths); if ($sum > 0) { $scale = $contentW / $sum; foreach ($widths as $i => $w) $widths[$i] = $w * $scale; }
        return $widths;
    }

    private static function wrapText(string $text, float $width, float $fontSize): array {
        $text = trim($text); if ($text === '') return [''];
        $maxChars = max(1, (int)floor($width / max(1, $fontSize * 0.52)));
        $words = preg_split('/\s+/u', $text) ?: []; $lines = []; $line = '';
        foreach ($words as $word) {
            if ($word === '') continue;
            while (mb_strlen($word) > $maxChars) { if ($line !== '') { $lines[] = $line; $line = ''; } $lines[] = mb_substr($word, 0, $maxChars); $word = mb_substr($word, $maxChars); }
            if ($word === '') continue;
            $candidate = $line === '' ? $word : ($line . ' ' . $word);
            if (mb_strlen($candidate) <= $maxChars) $line = $candidate;
            else { if ($line !== '') $lines[] = $line; $line = $word; }
        }
        if ($line !== '') $lines[] = $line;
        return $lines ?: [''];
    }

    private static function formatCell(string $column, $value): string {
        if ($value === null || $value === '') return '';
        if (in_array($column, ['created_at','sent_at','received_at','installed_at','loan_date_display','updated_at','reported_date','tanggal_mutasi'], true)) {
            $ts = strtotime((string)$value); if ($ts !== false) return date('Y-m-d H:i', $ts);
        }
        if (in_array($column, ['status','condition_status','type','transaction_type','action'], true)) return \App\Helpers\UIHelper::statusLabel((string)$value);
        return (string)$value;
    }
}
