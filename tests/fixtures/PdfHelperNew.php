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
        $converted = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
        return $converted;
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

        $colW = $contentW / max(1, $cols);
        $rowH = 16;
        $headerH = 18;

        // Branding
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

        // Draw branding header
        $drawBranding = function () use (&$current, &$y, $pageW, $margin, $contentW, $appName, $appSystem, $companyName, $logoJpg) {
            // Draw logo or placeholder
            if ($logoJpg) {
                // We will draw the image using XObject /Im1
                // Logo bounding box: 30x30
                $current[] = "q 30 0 0 30 " . $margin . " " . ($y - 30) . " cm /Im1 Do Q";
            } else {
                // Draw a colored box
                $current[] = "0.1 0.4 0.8 rg " . $margin . " " . ($y - 30) . " 30 30 re f";
                $current[] = "BT /F2 10 Tf 1 1 1 rg " . ($margin + 5) . " " . ($y - 20) . " Td (" . self::escText($appName) . ") Tj ET";
            }

            // Company text
            $current[] = "BT /F2 14 Tf 0 0 0 rg " . ($margin + 40) . " " . ($y - 12) . " Td (" . self::escText($appName) . ") Tj ET";
            $current[] = "BT /F1 10 Tf 0.3 0.3 0.3 rg " . ($margin + 40) . " " . ($y - 24) . " Td (" . self::escText($appSystem) . ") Tj ET";
            
            // Right side PT BIG
            // We approximate string width (font 12) ~ 6 points per char
            $compLen = strlen($companyName) * 6;
            $current[] = "BT /F2 12 Tf 0 0 0 rg " . ($pageW - $margin - $compLen - 10) . " " . ($y - 18) . " Td (" . self::escText($companyName) . ") Tj ET";

            // Draw line separator
            $current[] = "0.8 0.8 0.8 RG 1 w " . $margin . " " . ($y - 35) . " " . ($pageW - $margin) . " " . ($y - 35) . " S";
            
            $y -= 50;
        };

        $drawReportHeader = function () use (&$current, &$y, $margin, $title, $metadata, $contentW) {
            $y -= 10;
            $current[] = "BT /F2 14 Tf 0 0 0 rg " . $margin . " " . $y . " Td (" . self::escText(mb_strtoupper($title)) . ") Tj ET";
            $y -= 15;

            // Metadata Grid (2 columns max)
            $leftX = $margin;
            $rightX = $margin + ($contentW / 2);
            $i = 0;
            foreach ($metadata as $k => $v) {
                $curX = ($i % 2 === 0) ? $leftX : $rightX;
                $current[] = "BT /F1 9 Tf 0.3 0.3 0.3 rg " . $curX . " " . $y . " Td (" . self::escText($k) . " : ) Tj ET";
                $current[] = "BT /F2 9 Tf 0 0 0 rg " . ($curX + 70) . " " . $y . " Td (" . self::escText($v) . ") Tj ET";
                if ($i % 2 !== 0) {
                    $y -= 12;
                }
                $i++;
            }
            if ($i % 2 !== 0) {
                $y -= 12;
            }
            $y -= 10;
        };

        $drawTableHeader = function () use (&$current, &$y, $headers, $colW, $headerH, $margin, $contentW) {
            $x = $margin;
            $current[] = "0.2 0.3 0.4 rg " . $margin . " " . ($y - $headerH) . " " . $contentW . " $headerH re f";
            foreach ($headers as $h) {
                $w = $colW - 6;
                $current[] = "BT /F2 9 Tf 1 1 1 rg " . ($x + 3) . " " . ($y - 12) . " Td (" . self::escText(self::truncate($h, $w, 9)) . ") Tj ET";
                $x += $colW;
            }
            $y -= $headerH;
        };

        // Initialize First Page
        $drawBranding();
        $drawReportHeader();

        if (empty($rows)) {
            $y -= 20;
            $current[] = "BT /F1 10 Tf 0.5 0.5 0.5 rg " . $margin . " " . $y . " Td (Tidak ada data untuk kriteria yang dipilih.) Tj ET";
        } else {
            $drawTableHeader();
            $rowIndex = 0;
            foreach ($rows as $row) {
                if ($y < $margin + 20) {
                    $flushPage();
                    $y = $top;
                    $drawBranding(); // Header branding repeat per user instruction
                    $y -= 10; // add space
                    $drawTableHeader();
                }

                // Zebra striping
                if ($rowIndex % 2 === 0) {
                    $current[] = "0.95 0.96 0.98 rg " . $margin . " " . ($y - $rowH) . " " . $contentW . " $rowH re f";
                }
                
                $x = $margin;
                foreach ($headers as $i => $h) {
                    $col = $columns[$i] ?? $h;
                    $raw = is_array($row) ? ($row[$col] ?? '') : '';
                    $val = self::formatCell($col, $raw);
                    $current[] = "BT /F1 8 Tf 0.1 0.1 0.1 rg " . ($x + 3) . " " . ($y - 11) . " Td (" . self::escText(self::truncate((string)$val, $colW - 6, 8)) . ") Tj ET";
                    $x += $colW;
                }
                
                // Border bottom
                $current[] = "0.85 0.87 0.90 RG 0.5 w " . $margin . " " . ($y - $rowH) . " " . ($margin + $contentW) . " " . ($y - $rowH) . " S";
                
                $y -= $rowH;
                $rowIndex++;
            }
        }
        $flushPage();

        $totalPages = count($pages);

        // PDF Structure
        $contentStreams = [];
        $pageObjects = [];
        $kids = [];
        $objId = 3; // 1=Catalog, 2=Pages
        $pageno = 1;

        // If logo exists, we will define XObject for it
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
            $footerRight = "Dicetak: $generatedAt | Halaman $pageno dari $totalPages";
            $fRightLen = strlen($footerRight) * 4;

            $footerStr = "BT /F1 8 Tf 0.5 0.5 0.5 rg " . $margin . " 20 Td (" . self::escText($footerText) . ") Tj ET\n";
            $footerStr .= "BT /F1 8 Tf 0.5 0.5 0.5 rg " . ($pageW - $margin - $fRightLen - 20) . " 20 Td (" . self::escText($footerRight) . ") Tj ET";

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

        // Build PDF
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
            if (isset($offsets[$id])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
            } else {
                $pdf .= "0000000000 00000 f \n"; // Should not happen if continuous
            }
        }
        $pdf .= "trailer\n<< /Size " . (max(array_keys($final)) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xrefPos . "\n%%EOF";
        return $pdf;
    }

    private static function formatCell(string $column, $value): string {
        if ($value === null || $value === '') {
            return '';
        }
        if (in_array($column, ['created_at', 'sent_at', 'received_at', 'installed_at', 'loan_date_display', 'updated_at', 'reported_date', 'tanggal_mutasi'], true)) {
            $ts = strtotime((string)$value);
            if ($ts !== false) {
                return date('Y-m-d H:i', $ts);
            }
        }
        return (string)$value;
    }

    private static function truncate(string $text, float $width, float $fontSize): string {
        $maxChars = (int)floor($width / ($fontSize * 0.52));
        if (mb_strlen($text) > $maxChars) {
            return mb_substr($text, 0, max(1, $maxChars - 1)) . '…';
        }
        return $text;
    }
}
