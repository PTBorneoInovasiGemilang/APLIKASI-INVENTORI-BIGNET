<?php
namespace App\Helpers;

use App\Core\Database;

/**
 * Professional Branded PDF Writer (PDF 1.4) tanpa dependency.
 */
class PdfHelper {
    private static function sanitize(string $text): string {
        $text = str_replace(["\r", "\n"], ' ', $text);
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            $converted = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }

    private static function escText(string $text): string {
        return self::sanitize($text);
    }

    private static function getSetting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val !== false ? $val : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    public static function tableReport(
        string $title,
        array $headers,
        array $rows,
        array $metadata = [],
        array $columns = []
    ): string {
        $generatedAt = date('d F Y H:i');
        $cols = count($headers);
        $isLandscape = $cols > 6;
        $pageW = $isLandscape ? 842 : 595;
        $pageH = $isLandscape ? 595 : 842;
        $margin = 40;
        $contentW = $pageW - ($margin * 2);
        $headerH = 20;
        $fontSize = 8;
        $lineH = 10;
        $cellPadTop = 7;
        $cellPadBottom = 5;

        $appName = self::getSetting('app_short_name', 'BIG');
        $appSystem = 'Sistem Gudang';
        $companyName = self::getSetting('company_name', 'PT. BIG');
        $faviconPath = self::getSetting('favicon_path');

        $logoJpg = '';
        $logoW = 0;
        $logoH = 0;
        if ($faviconPath) {
            $path = __DIR__ . '/../../storage/private_uploads/' . $faviconPath;
            if (file_exists($path)) {
                $info = getimagesize($path);
                if ($info && $info['mime'] === 'image/jpeg') {
                    $logoJpg = file_get_contents($path);
                    $logoW = $info[0];
                    $logoH = $info[1];
                }
            }
        }

        // Column widths are proportional to header length, with a fixed compact No column.
        $colWidths = [];
        $remainingW = $contentW;
        $remainingCols = $cols;
        $weights = [];
        $totalWeight = 0;
        foreach ($headers as $i => $h) {
            if (strtolower(trim($h)) === 'no') {
                $colWidths[$i] = 30;
                $remainingW -= 30;
                $remainingCols--;
                $weights[$i] = 0;
            } else {
                $weights[$i] = max(4, mb_strlen(trim($h)));
                $totalWeight += $weights[$i];
            }
        }
        foreach ($headers as $i => $h) {
            if (!isset($colWidths[$i])) {
                $colWidths[$i] = max(45, ($remainingW * $weights[$i]) / max(1, $totalWeight));
            }
        }
        // Normalize in case min widths pushed total above content width.
        $sumW = array_sum($colWidths);
        if ($sumW > $contentW) {
            $scale = $contentW / $sumW;
            foreach ($colWidths as $i => $w) $colWidths[$i] = max(26, $w * $scale);
        }

        $pages = [];
        $current = [];
        $top = $pageH - $margin;
        $y = $top;
        $flushPage = function () use (&$pages, &$current) {
            if (!empty($current)) {
                $pages[] = $current;
                $current = [];
            }
        };

        $drawBranding = function () use (&$current, &$y, $pageW, $margin, $appName, $appSystem, $companyName, $logoJpg, $logoW, $logoH) {
            if ($logoJpg && $logoW > 0 && $logoH > 0) {
                $ratio = min(40 / $logoW, 40 / $logoH);
                $dw = $logoW * $ratio;
                $dh = $logoH * $ratio;
                $offsetY = (40 - $dh) / 2;
                $current[] = "q " . number_format($dw, 2, '.', '') . " 0 0 " . number_format($dh, 2, '.', '') . " " . $margin . " " . ($y - 40 + $offsetY) . " cm /Im1 Do Q";
            } else {
                $current[] = "0.1 0.4 0.8 rg " . $margin . " " . ($y - 35) . " 35 35 re f";
                $current[] = "BT /F2 12 Tf 1 1 1 rg " . ($margin + 6) . " " . ($y - 23) . " Td (" . self::escText($appName) . ") Tj ET";
            }
            $current[] = "BT /F2 16 Tf 0 0 0 rg " . ($margin + 50) . " " . ($y - 12) . " Td (" . self::escText($appName) . ") Tj ET";
            $current[] = "BT /F1 10 Tf 0.4 0.4 0.4 rg " . ($margin + 50) . " " . ($y - 26) . " Td (" . self::escText($appSystem) . ") Tj ET";
            $compFontSize = 14;
            $len = strlen($companyName);
            if ($len > 22) $compFontSize = 12;
            if ($len > 30) $compFontSize = 10;
            $compLen = $len * ($compFontSize * 0.65);
            $current[] = "BT /F2 $compFontSize Tf 0 0 0 rg " . ($pageW - $margin - $compLen) . " " . ($y - 18) . " Td (" . self::escText($companyName) . ") Tj ET";
            $current[] = "0.8 0.8 0.8 RG 0.5 w " . $margin . " " . ($y - 42) . " " . ($pageW - $margin) . " " . ($y - 42) . " S";
            $y -= 55;
        };

        $drawReportHeader = function () use (&$current, &$y, $margin, $title, $metadata, $contentW) {
            $y -= 8;
            $current[] = "BT /F2 14 Tf 0 0 0 rg " . $margin . " " . $y . " Td (" . self::escText(mb_strtoupper($title)) . ") Tj ET";
            $y -= 16;
            $leftX = $margin;
            $rightX = $margin + ($contentW / 2);
            $i = 0;
            foreach ($metadata as $k => $v) {
                $curX = ($i % 2 === 0) ? $leftX : $rightX;
                $current[] = "BT /F1 9 Tf 0.3 0.3 0.3 rg " . $curX . " " . $y . " Td (" . self::escText($k) . " : ) Tj ET";
                $current[] = "BT /F2 9 Tf 0 0 0 rg " . ($curX + 70) . " " . $y . " Td (" . self::escText($v) . ") Tj ET";
                if ($i % 2 !== 0) $y -= 11;
                $i++;
            }
            if ($i % 2 !== 0) $y -= 11;
            $y -= 6;
        };

        $drawTableHeader = function () use (&$current, &$y, $headers, $colWidths, $headerH, $margin, $contentW) {
            $x = $margin;
            $current[] = "0.2 0.3 0.4 rg " . $margin . " " . ($y - $headerH) . " " . $contentW . " $headerH re f";
            foreach ($headers as $i => $h) {
                $cW = $colWidths[$i];
                $current[] = "BT /F2 8 Tf 1 1 1 rg " . ($x + 3) . " " . ($y - 13) . " Td (" . self::escText(self::truncate($h, $cW - 5, 8)) . ") Tj ET";
                $x += $cW;
            }
            $y -= $headerH;
        };

        $drawBranding();
        $drawReportHeader();

        if (empty($rows)) {
            $y -= 20;
            $current[] = "BT /F1 10 Tf 0.5 0.5 0.5 rg " . $margin . " " . $y . " Td (Tidak ada data untuk kriteria yang dipilih.) Tj ET";
        } else {
            $drawTableHeader();
            $rowIndex = 0;
            foreach ($rows as $row) {
                $cellLines = [];
                $maxLines = 1;
                foreach ($headers as $i => $h) {
                    $col = $columns[$i] ?? $h;
                    $raw = is_array($row) ? ($row[$col] ?? '') : '';
                    $val = self::formatCell($col, $raw);
                    $lines = self::wrapText((string)$val, max(20, $colWidths[$i] - 8), $fontSize);
                    $cellLines[$i] = $lines;
                    $maxLines = max($maxLines, count($lines));
                }

                $rowH = ($maxLines * $lineH) + $cellPadTop + $cellPadBottom;
                $available = $y - ($margin + 40);
                if ($rowH > $available && $rowH < ($pageH - 2 * $margin)) {
                    $flushPage();
                    $y = $top;
                    $drawBranding();
                    $drawTableHeader();
                } elseif ($y - $rowH < $margin + 40) {
                    $flushPage();
                    $y = $top;
                    $drawBranding();
                    $drawTableHeader();
                }

                if ($rowIndex % 2 === 0) {
                    $current[] = "0.95 0.96 0.98 rg " . $margin . " " . ($y - $rowH) . " " . $contentW . " $rowH re f";
                }

                $x = $margin;
                foreach ($headers as $i => $h) {
                    $cW = $colWidths[$i];
                    foreach ($cellLines[$i] as $lineIndex => $line) {
                        $textY = $y - $cellPadTop - 8 - ($lineIndex * $lineH);
                        $current[] = "BT /F1 $fontSize Tf 0.1 0.1 0.1 rg " . ($x + 3) . " " . $textY . " Td (" . self::escText($line) . ") Tj ET";
                    }
                    $x += $cW;
                }

                $current[] = "0.85 0.87 0.90 RG 0.5 w " . $margin . " " . ($y - $rowH) . " " . ($margin + $contentW) . " " . ($y - $rowH) . " S";
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
        $pageno = 1;

        $imageId = 0;
        if ($logoJpg) {
            $imageId = $objId;
            $objId++;
        }

        foreach ($pages as $lines) {
            $pageId = $objId;
            $contentId = $objId + 1;
            $objId += 2;
            $kids[] = "$pageId 0 R";

            $footerText = "$companyName — $appSystem";
            $footerRight = "Dicetak: $generatedAt | Hal. $pageno dari $totalPages";
            $fRightLen = strlen($footerRight) * (8 * 0.55);
            $footerStr = "0.8 0.8 0.8 RG 0.5 w " . $margin . " 35 " . ($pageW - $margin) . " 35 S\n";
            $footerStr .= "BT /F1 8 Tf 0.5 0.5 0.5 rg " . $margin . " 22 Td (" . self::escText($footerText) . ") Tj ET\n";
            $footerStr .= "BT /F1 8 Tf 0.5 0.5 0.5 rg " . ($pageW - $margin - $fRightLen) . " 22 Td (" . self::escText($footerRight) . ") Tj ET";

            $streamBody = implode("\n", $lines) . "\n" . $footerStr;
            $contentStreams[$contentId] = $streamBody;
            $xobjRef = $logoJpg ? " /XObject << /Im1 $imageId 0 R >> " : "";
            $pageObjects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pageW $pageH] /Resources << /Font << /F1 " . ($objId + 1) . " 0 R /F2 " . $objId . " 0 R >> $xobjRef >> /Contents $contentId 0 R >>";
            $pageno++;
        }

        $fontBoldId = $objId;
        $fontRegId = $objId + 1;
        $final = [];
        $final[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $final[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . count($pages) . " >>";
        if ($logoJpg) {
            $final[$imageId] = "<< /Type /XObject /Subtype /Image /Width $logoW /Height $logoH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($logoJpg) . " >>\nstream\n" . $logoJpg . "\nendstream";
        }
        foreach ($pageObjects as $id => $spec) $final[$id] = $spec;
        $final[$fontBoldId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
        $final[$fontRegId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        foreach ($contentStreams as $id => $body) $final[$id] = $body;

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        ksort($final);
        foreach ($final as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n";
            if (isset($contentStreams[$id])) {
                $pdf .= "<< /Length " . strlen($body) . " >>\nstream\n" . $body . "\nendstream\n";
            } else {
                $pdf .= $body . "\n";
            }
            $pdf .= "endobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (max(array_keys($final)) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($id = 1; $id <= max(array_keys($final)); $id++) {
            $pdf .= isset($offsets[$id]) ? sprintf("%010d 00000 n \n", $offsets[$id]) : "0000000000 00000 f \n";
        }
        $pdf .= "trailer\n<< /Size " . (max(array_keys($final)) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";
        return $pdf;
    }

    private static function formatCell(string $column, $value): string {
        if ($value === null || $value === '') return '';
        if (in_array($column, ['created_at', 'sent_at', 'received_at', 'installed_at', 'loan_date_display', 'updated_at', 'reported_date', 'tanggal_mutasi'], true)) {
            $ts = strtotime((string)$value);
            if ($ts !== false) return date('Y-m-d H:i', $ts);
        }
        if (in_array($column, ['status', 'condition_status', 'type', 'transaction_type', 'action'], true)) {
            return \App\Helpers\UIHelper::statusLabel((string)$value);
        }
        return (string)$value;
    }

    private static function wrapText(string $text, float $width, float $fontSize): array {
        $text = trim($text);
        if ($text === '') return [''];
        $maxChars = max(4, (int)floor($width / ($fontSize * 0.52)));
        $paragraphs = preg_split('/\r?\n/', $text) ?: [''];
        $lines = [];
        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/', trim($paragraph)) ?: [''];
            $line = '';
            foreach ($words as $word) {
                if ($word === '') continue;
                if (mb_strlen($word) > $maxChars) {
                    if ($line !== '') {
                        $lines[] = $line;
                        $line = '';
                    }
                    while (mb_strlen($word) > $maxChars) {
                        $lines[] = mb_substr($word, 0, $maxChars);
                        $word = mb_substr($word, $maxChars);
                    }
                    if ($word !== '') $line = $word;
                    continue;
                }
                $candidate = $line === '' ? $word : $line . ' ' . $word;
                if (mb_strlen($candidate) <= $maxChars) {
                    $line = $candidate;
                } else {
                    if ($line !== '') $lines[] = $line;
                    $line = $word;
                }
            }
            if ($line !== '' || empty($lines)) $lines[] = $line;
        }
        return $lines ?: [''];
    }

    private static function truncate(string $text, float $width, float $fontSize): string {
        $maxChars = max(1, (int)floor($width / ($fontSize * 0.52)));
        if (mb_strlen($text) <= $maxChars) return $text;
        return mb_substr($text, 0, max(1, $maxChars - 1)) . '…';
    }
}
