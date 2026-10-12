<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate study notes from extracted PDF text.
     *
     * @param string $sourceText The extracted text from the PDF
     * @param string $materialTitle The title of the source material
     * @return string The generated notes in markdown format
     * @throws \Exception If generation fails
     */
    public function generateNotes(string $sourceText, string $materialTitle): string;

    /**
     * Get the provider name for tracking purposes.
     *
     * @return string Provider identifier (e.g., 'gemini', 'groq')
     */
    public function getProviderName(): string;

    /**
     * Get the model name being used.
     *
     * @return string Model identifier
     */
    public function getModelName(): string;

    /**
     * Check if the provider is properly configured.
     *
     * @return bool True if API key and configuration are present
     */
    public function isConfigured(): bool;

    /**
     * Generate a multiple-choice quiz from extracted PDF text.
     *
     * @param string $sourceText The extracted text from the PDF
     * @param string $materialTitle The title of the source material
     * @param int $questionCount Number of questions to generate
     * @param int $optionsPerQuestion Number of options per question (default 4)
     * @return array Structured quiz data
     * @throws \Exception If generation fails
     */
    public function generateQuiz(string $sourceText, string $materialTitle, int $questionCount, int $optionsPerQuestion = 4): array;
}
