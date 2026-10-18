<?php

namespace App\Services\Concerns;

trait HandlesDocumentChunking
{
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
     * Group sections into balanced chunks for comprehensive multi-part note generation.
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

    /**
     * Segment document for uniform question coverage across the entire material from first to last page.
     *
     * @param string $text
     * @param int $questionCount
     * @return array Array of segments with target questions and text
     */
    public function getQuizSegments(string $text, int $questionCount): array
    {
        $sections = $this->splitIntoSections($text);
        $totalSections = count($sections);

        if ($totalSections <= 10 || $questionCount <= 5) {
            $first = !empty($sections) ? $sections[0]['number'] : 1;
            $last = !empty($sections) ? end($sections)['number'] : 1;
            $type = !empty($sections) ? $sections[0]['type'] : 'PAGE';
            return [
                [
                    'segment_number' => 1,
                    'total_segments' => 1,
                    'type' => $type,
                    'start' => $first,
                    'end' => $last,
                    'count' => $totalSections,
                    'target_questions' => $questionCount,
                    'text' => $text,
                ]
            ];
        }

        $numSegments = max(2, min((int) floor($questionCount / 5), 4));
        $chunkSize = (int) ceil($totalSections / $numSegments);
        $chunksRaw = array_chunk($sections, $chunkSize);

        if (count($chunksRaw) > 1 && count(end($chunksRaw)) < 3) {
            $last = array_pop($chunksRaw);
            $chunksRaw[count($chunksRaw) - 1] = array_merge($chunksRaw[count($chunksRaw) - 1], $last);
        }

        $finalNumSegments = count($chunksRaw);
        $baseQuestions = intdiv($questionCount, $finalNumSegments);
        $remainder = $questionCount % $finalNumSegments;

        $segments = [];
        foreach ($chunksRaw as $idx => $chunkItems) {
            $targetQ = $baseQuestions + ($idx < $remainder ? 1 : 0);
            $first = $chunkItems[0]['number'];
            $last = end($chunkItems)['number'];
            $type = $chunkItems[0]['type'];
            $segText = implode("\n\n", array_column($chunkItems, 'raw'));

            $segments[] = [
                'segment_number' => $idx + 1,
                'total_segments' => $finalNumSegments,
                'type' => $type,
                'start' => $first,
                'end' => $last,
                'count' => count($chunkItems),
                'target_questions' => $targetQ,
                'text' => $segText,
            ];
        }

        return $segments;
    }
}
