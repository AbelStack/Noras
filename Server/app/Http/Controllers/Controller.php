<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Convert technical AI and network exceptions into clear, user-friendly messages.
     */
    protected function formatAiErrorMessage(\Throwable $e, string $type = 'content'): string
    {
        $msg = $e->getMessage();

        // Timeout or connection failure
        if (stripos($msg, 'timed out') !== false || stripos($msg, 'cURL error 28') !== false || stripos($msg, 'ConnectionException') !== false) {
            return "The AI service took too long to respond while generating {$type}. Please try again in a few moments.";
        }

        // Rate limits or quota exhaustion
        if (stripos($msg, 'quota') !== false || stripos($msg, 'rate limit') !== false || stripos($msg, '429') !== false || stripos($msg, 'resource has been exhausted') !== false) {
            return "The AI service is temporarily busy due to high request volume. Please wait a minute and try again.";
        }

        // Insufficient readable text
        if (stripos($msg, 'insufficient text') !== false || stripos($msg, 'minimum 100 characters') !== false || stripos($msg, 'empty') !== false) {
            return "This document does not contain enough readable text to generate {$type}. Please make sure the uploaded file has text content.";
        }

        // Configuration or API key missing
        if (stripos($msg, 'not configured') !== false || stripos($msg, 'API key') !== false) {
            return "The AI service is not properly configured. Please contact the administrator.";
        }

        // JSON or parsing error
        if (stripos($msg, 'JSON') !== false || stripos($msg, 'missing questions') !== false || stripos($msg, 'No questions') !== false) {
            return "The AI was unable to structure the questions properly from this document. Please try again.";
        }

        // Any other technical cURL / HTTP library messages
        if (stripos($msg, 'http') !== false || stripos($msg, 'curl') !== false || stripos($msg, 'json_last_error') !== false) {
            return "Unable to generate {$type} at this moment. Please try again.";
        }

        // Clean user-facing string: strip common technical prefixes
        $clean = preg_replace('/^(Gemini API error:\s*|Groq API error:\s*|Failed to generate (note|quiz):\s*)/i', '', $msg);
        return !empty(trim($clean)) ? trim($clean) : "Failed to generate {$type}. Please try again.";
    }
}
