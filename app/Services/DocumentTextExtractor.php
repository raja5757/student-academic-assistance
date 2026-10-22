<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class DocumentTextExtractor
{
    public function extract(string $path, ?string $mimeType = null): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $text = match ($extension) {
            'txt' => file_get_contents($path) ?: '',
            'docx' => $this->extractDocx($path),
            'pdf' => $this->extractPdfHeuristic($path),
            default => '',
        };

        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode($text)));

        if ($text === '') {
            throw new RuntimeException('Unable to read this document. Please upload a supported TXT, PDF, or DOCX file.');
        }

        return $text;
    }

    private function extractDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        return strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml));
    }

    private function extractPdfHeuristic(string $path): string
    {
        $contents = file_get_contents($path) ?: '';
        preg_match_all('/\(([^()]*)\)/', $contents, $matches);

        $text = implode(' ', $matches[1] ?? []);
        // Remove binary garbage and non-ASCII characters to prevent DB encoding errors
        return preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $text);
    }
}
