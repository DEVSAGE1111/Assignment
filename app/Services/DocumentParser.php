<?php

namespace App\Services;

class DocumentParser
{
    /**
     * Parse a .docx file and return its raw text.
     */
    public static function parseDocx(string $filePath): string
    {
        if (! file_exists($filePath)) {
            return '';
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();

                // Load XML and extract paragraph text
                try {
                    $xml = new \SimpleXMLElement($data);
                    $namespaces = $xml->getNamespaces(true);
                    $xml->registerXPathNamespace('w', $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                    $texts = $xml->xpath('//w:t');

                    $fullText = '';
                    foreach ($texts as $t) {
                        $fullText .= (string) $t.' ';
                    }

                    return trim($fullText);
                } catch (\Exception $e) {
                    return '';
                }
            }
            $zip->close();
        }

        return '';
    }

    /**
     * Parse an .xlsx file and return its rows.
     */
    public static function parseXlsx(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Get shared strings
        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            try {
                $xmlData = $zip->getFromIndex($index);
                $xml = new \SimpleXMLElement($xmlData);
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) { // Rich text
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        // 2. Get sheet data
        $rows = [];
        if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
            try {
                $xmlData = $zip->getFromIndex($index);
                $xml = new \SimpleXMLElement($xmlData);
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    foreach ($row->c as $c) {
                        $value = isset($c->v) ? (string) $c->v : '';
                        $type = isset($c['t']) ? (string) $c['t'] : '';

                        if ($type === 's' && $value !== '') {
                            $value = $sharedStrings[(int) $value] ?? '';
                        }

                        // Parse the column letter from reference (e.g., A1, B12)
                        $ref = (string) $c['r'];
                        $colLetters = preg_replace('/[0-9]/', '', $ref);
                        $colIdx = self::columnLetterToIndex($colLetters);
                        $rowData[$colIdx] = $value;
                    }

                    // Fill any gaps and pad row array
                    $maxIdx = count($rowData) > 0 ? max(array_keys($rowData)) : -1;
                    $filledRow = [];
                    for ($i = 0; $i <= $maxIdx; $i++) {
                        $filledRow[$i] = $rowData[$i] ?? '';
                    }
                    $rows[] = $filledRow;
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        $zip->close();

        return $rows;
    }

    /**
     * Parse a CSV file and return its rows.
     */
    public static function parseCsv(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }

        return $rows;
    }

    /**
     * Helper to convert Excel column letters to 0-based indices (e.g. A->0, B->1, Z->25, AA->26)
     */
    private static function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }
}
