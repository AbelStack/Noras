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

            // Parse the PDF page-by-page
            $pdf = $this->parser->parseFile($fullPath);
            $pages = $pdf->getPages();

            $pageTexts = [];
            if (!empty($pages)) {
                foreach ($pages as $index => $page) {
                    $pageNum = $index + 1;
                    $pageRaw = $page->getText();
                    $pageClean = $this->cleanText($pageRaw);
                    if (!empty(trim($pageClean))) {
                        $pageTexts[] = "--- [PAGE {$pageNum}] ---\n" . $pageClean;
                    }
                }
            }

            if (!empty($pageTexts)) {
                $text = implode("\n\n", $pageTexts);
            } else {
                // Fallback to global getText if page-by-page returned empty
                $text = $this->cleanText($pdf->getText());
            }

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
     * Extract slide text from a PowerPoint presentation (PPTX format) sequentially.
     */
    private function extractPptxText(string $fullPath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== true) {
            throw new Exception('Could not open presentation file for text extraction.');
        }

        // Collect and naturally sort slides numerically (Slide 1, Slide 2, ..., Slide N)
        $slideFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#i', $name, $matches)) {
                $slideFiles[(int) $matches[1]] = $name;
            }
        }
        ksort($slideFiles);

        $allText = [];
        foreach ($slideFiles as $slideNum => $slideName) {
            $xmlContent = $zip->getFromName($slideName);
            if ($xmlContent && preg_match_all('#<a:t[^>]*>(.*?)</a:t>#is', $xmlContent, $matches)) {
                $slideText = implode(' ', $matches[1]);
                $cleanSlide = $this->cleanText(html_entity_decode(trim($slideText)));
                if (!empty($cleanSlide)) {
                    $allText[] = "--- [SLIDE {$slideNum}] ---\n" . $cleanSlide;
                }
            }
        }
        $zip->close();

        if (empty($allText)) {
            throw new Exception('No readable text could be extracted from the presentation slides');
        }

        return implode("\n\n", $allText);
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

    /**
     * Parse extracted text into structured individual sections (pages or slides).
     *
     * @param string $text
     * @return array Array of ['type' => string, 'number' => int, 'text' => string, 'raw' => string]
     */
    public function splitIntoSections(string $text): array
    {
        $re = '/--- \[(PAGE|SLIDE) (\d+)\] ---\s*([\s\S]*?)(?=(?:--- \[(?:PAGE|SLIDE) \d+\] ---)|$)/';
        if (preg_match_all($re, $text, $matches, PREG_SET_ORDER)) {
            $sections = [];
            foreach ($matches as $match) {
                $clean = trim($match[3]);
                if (!empty($clean)) {
                    $sections[] = [
                        'type' => $match[1],
                        'number' => (int) $match[2],
                        'text' => $clean,
                        'raw' => trim($match[0]),
                    ];
                }
            }
            if (!empty($sections)) {
                return $sections;
            }
        }

        // Fallback for unpaginated or raw text: split by paragraph chunks of ~12,000 characters
        $paragraphs = explode("\n\n", $text);
        $sections = [];
        $current = '';
        $chunkIndex = 1;

        foreach ($paragraphs as $para) {
            if (mb_strlen($current . "\n\n" . $para, 'UTF-8') > 12000 && !empty($current)) {
                $sections[] = [
                    'type' => 'SECTION',
                    'number' => $chunkIndex++,
                    'text' => trim($current),
                    'raw' => "--- [SECTION " . ($chunkIndex - 1) . "] ---\n" . trim($current),
                ];
                $current = $para;
            } else {
                $current .= ($current ? "\n\n" : "") . $para;
            }
        }

        if (!empty(trim($current))) {
            $sections[] = [
                'type' => 'SECTION',
                'number' => $chunkIndex,
                'text' => trim($current),
                'raw' => "--- [SECTION {$chunkIndex}] ---\n" . trim($current),
            ];
        }

        return $sections;
    }

    /**
     * Group sections into balanced chunks for comprehensive multi-part generation.
     *
     * @param string $text
     * @param int $targetPerChunk
     * @return array Array of chunks, each with 'type', 'start', 'end', 'count', and 'text'
     */
    public function getChunkedSections(string $text, int $targetPerChunk = 15): array
    {
        $sections = $this->splitIntoSections($text);
        $total = count($sections);

        if ($total <= $targetPerChunk) {
            $first = !empty($sections) ? $sections[0]['number'] : 1;
            $last = !empty($sections) ? end($sections)['number'] : 1;
            $type = !empty($sections) ? $sections[0]['type'] : 'PAGE';
            return [
                [
                    'type' => $type,
                    'start' => $first,
                    'end' => $last,
                    'count' => $total,
                    'text' => $text,
                ]
            ];
        }

        // Calculate optimal balanced number of chunks so no chunk is left with just 1 or 2 pages
        $numChunks = max(2, (int) ceil($total / $targetPerChunk));
        $chunkSize = (int) ceil($total / $numChunks);
        $chunksRaw = array_chunk($sections, $chunkSize);

        // If the last chunk is too small (< 4 sections) and we have more than 1 chunk, merge it with previous
        if (count($chunksRaw) > 1 && count(end($chunksRaw)) < 4) {
            $last = array_pop($chunksRaw);
            $chunksRaw[count($chunksRaw) - 1] = array_merge($chunksRaw[count($chunksRaw) - 1], $last);
        }

        $chunks = [];
        foreach ($chunksRaw as $chunkItems) {
            $first = $chunkItems[0]['number'];
            $last = end($chunkItems)['number'];
            $type = $chunkItems[0]['type'];
            $chunkText = implode("\n\n", array_column($chunkItems, 'raw'));

            $chunks[] = [
                'type' => $type,
                'start' => $first,
                'end' => $last,
                'count' => count($chunkItems),
                'text' => $chunkText,
            ];
        }

        return $chunks;
    }
}
