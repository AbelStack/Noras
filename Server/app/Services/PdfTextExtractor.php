<?php

namespace App\Services;

use App\Models\CourseMaterial;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Exception;

class PdfTextExtractor
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from a PDF course material.
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

        // Get the file path
        $filePath = $material->file_path;
        
        if (!Storage::disk('private')->exists($filePath)) {
            throw new Exception('Course material file not found');
        }

        // Get the full system path
        $fullPath = Storage::disk('private')->path($filePath);

        try {
            // Parse the PDF
            $pdf = $this->parser->parseFile($fullPath);
            
            // Extract text from all pages
            $text = $pdf->getText();
            
            // Clean up the text
            $text = $this->cleanText($text);
            
            if (empty(trim($text))) {
                throw new Exception('No text could be extracted from the PDF');
            }
            
            return $text;
            
        } catch (Exception $e) {
            throw new Exception('Failed to extract text from PDF: ' . $e->getMessage());
        }
    }

    /**
     * Clean extracted text by removing excessive whitespace and normalizing line breaks.
     * 
     * @param string $text
     * @return string
     */
    private function cleanText(string $text): string
    {
        // Normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove excessive blank lines (more than 2 consecutive newlines)
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        
        // Trim whitespace from each line
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);
        
        // Remove any remaining excessive spaces
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
     * Estimate token count (rough approximation: 1 token ≈ 4 characters).
     * 
     * @param string $text
     * @return int
     */
    public function estimateTokenCount(string $text): int
    {
        return (int) ceil($this->getCharacterCount($text) / 4);
    }
}
