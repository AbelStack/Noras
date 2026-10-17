<?php

namespace App\Services;

use App\Models\CourseMaterial;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Exception;
use ZipArchive;

class PdfTextExtractor
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from a course material (PDF or PPTX).
     * 
     * @param CourseMaterial $material
     * @return string
     * @throws Exception
     */
    public function extractText(CourseMaterial $material): string
    {
        // Verify material is published
        if ($material->status !== 'published') {
            throw new Exception('Cannot extract text from unpublished material');
        }

        $filePath = $material->file_path;
        $tempPath = null;
        $fullPath = null;

        // Check local disk first
        if (Storage::disk('local')->exists($filePath)) {
            $fullPath = Storage::disk('local')->path($filePath);
        } else {
            // Check Cloudflare R2
            try {
                if (Storage::disk('r2')->exists($filePath)) {
                    $content = Storage::disk('r2')->get($filePath);
                    $tempPath = tempnam(sys_get_temp_dir(), 'noras_doc_');
                    file_put_contents($tempPath, $content);
                    $fullPath = $tempPath;
                }
            } catch (\Exception $e) {
                // fall through
            }
        }

        if (!$fullPath || !file_exists($fullPath)) {
            throw new Exception('Course material file not found on storage server');
        }

        try {
            $extension = strtolower(pathinfo($material->original_filename, PATHINFO_EXTENSION));
            $isPpt = in_array($extension, ['ppt', 'pptx']) || str_contains($material->mime_type ?? '', 'presentation');

            if ($isPpt) {
                return $this->extractPptxText($fullPath);
            }

            // Parse the PDF
            $pdf = $this->parser->parseFile($fullPath);
            $text = $pdf->getText();
            $text = $this->cleanText($text);

            if (empty(trim($text))) {
                throw new Exception('No text could be extracted from the PDF');
            }

            return $text;

        } catch (Exception $e) {
            throw new Exception('Failed to extract text from document: ' . $e->getMessage());
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Extract slide text from a PowerPoint presentation (PPTX format).
     */
    private function extractPptxText(string $fullPath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== true) {
            throw new Exception('Could not open presentation file for text extraction.');
        }

        $allText = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide\d+\.xml$#i', $name)) {
                $xmlContent = $zip->getFromIndex($i);
                if ($xmlContent && preg_match_all('#<a:t[^>]*>(.*?)</a:t>#is', $xmlContent, $matches)) {
                    $slideText = implode(' ', $matches[1]);
                    if (!empty(trim($slideText))) {
                        $allText[] = html_entity_decode(trim($slideText));
                    }
                }
            }
        }
        $zip->close();

        $text = implode("\n\n", $allText);
        $clean = $this->cleanText($text);

        if (empty($clean)) {
            throw new Exception('No readable text could be extracted from the presentation slides');
        }

        return $clean;
    }

    /**
     * Clean extracted text by removing excessive whitespace and normalizing line breaks.
     * 
     * @param string $text
     * @return string
     */
    private function cleanText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);
        $text = preg_replace('/ {2,}/', ' ', $text);

        return trim($text);
    }

    /**
     * Get the character count of extracted text.
     * 
     * @param string $text
     * @return int
     */
    public function getCharacterCount(string $text): int
    {
        return mb_strlen($text, 'UTF-8');
    }

    /**
     * Estimate token count from text (approximately 4 characters per token).
     * 
     * @param string $text
     * @return int
     */
    public function estimateTokenCount(string $text): int
    {
        return (int) ceil(mb_strlen($text, 'UTF-8') / 4);
    }
}
