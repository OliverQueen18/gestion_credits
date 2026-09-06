<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use ZipArchive;

class XlsxReader
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /**
     * @return list<array<string, mixed>>
     */
    public function rows(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Fichier introuvable : {$path}");
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Impossible d’ouvrir le fichier Excel : {$path}");
        }

        $shared = $this->sharedStrings($zip);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheet === false) {
            throw new RuntimeException('Feuille Excel introuvable.');
        }

        $xml = simplexml_load_string($sheet);
        $xml->registerXPathNamespace('m', self::NS);
        $rows = [];

        foreach ($xml->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $row->registerXPathNamespace('m', self::NS);
            $cells = [];
            $max = -1;
            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->columnIndex($ref);
                $max = max($max, $index);
                $cells[$index] = $this->cellValue($cell, $shared);
            }
            if ($max < 0) {
                continue;
            }
            $normalized = [];
            for ($i = 0; $i <= $max; $i++) {
                $normalized[] = $cells[$i] ?? null;
            }
            if (array_filter($normalized, fn ($v) => ! $this->isEmpty($v))) {
                $rows[] = $normalized;
            }
        }

        if ($rows === []) {
            return [];
        }

        $headers = array_map(fn ($h) => trim((string) $h), $rows[0]);
        $out = [];
        foreach (array_slice($rows, 1) as $row) {
            $assoc = [];
            foreach ($headers as $i => $header) {
                $assoc[$header] = $row[$i] ?? null;
            }
            $out[] = $assoc;
        }

        return $out;
    }

    public function excelDate(mixed $value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $epoch = new DateTimeImmutable('1899-12-30', new DateTimeZone('UTC'));
        $date = $epoch->modify('+'.(int) $value.' days');

        return $date->format('Y-m-d');
    }

    public function excelTime(mixed $value): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $seconds = (int) round(((float) $value) * 86400);
        $hours = intdiv($seconds, 3600) % 24;
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * @return list<string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $root = simplexml_load_string($xml);
        $root->registerXPathNamespace('m', self::NS);
        $strings = [];
        foreach ($root->xpath('//m:si') ?: [] as $si) {
            $texts = [];
            foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                $texts[] = (string) $t;
            }
            $strings[] = implode('', $texts);
        }

        return $strings;
    }

    private function cellValue(\SimpleXMLElement $cell, array $shared): mixed
    {
        $type = (string) $cell['t'];
        if ($type === 'inlineStr') {
            $texts = [];
            foreach ($cell->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                $texts[] = (string) $t;
            }

            return implode('', $texts);
        }

        $values = $cell->xpath('./*[local-name()="v"]');
        $raw = $values ? (string) $values[0] : null;
        if ($raw === null || $raw === '') {
            return null;
        }

        if ($type === 's') {
            return $shared[(int) $raw] ?? null;
        }

        if ($type === 'b') {
            return (bool) (int) $raw;
        }

        if ($type === 'str') {
            return $raw;
        }

        if (is_numeric($raw)) {
            return str_contains($raw, '.') ? (float) $raw : (int) $raw;
        }

        return $raw;
    }

    private function columnIndex(string $ref): int
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($ref)) ?? '';
        $n = 0;
        foreach (str_split($letters) as $c) {
            $n = $n * 26 + (ord($c) - 64);
        }

        return $n - 1;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }
}
