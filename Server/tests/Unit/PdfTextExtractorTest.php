<?php

namespace Tests\Unit;

use App\Models\CourseMaterial;
use App\Services\PdfTextExtractor;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PdfTextExtractorTest extends TestCase
{
    use RefreshDatabase;

    private PdfTextExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new PdfTextExtractor();
        Storage::fake('private');
    }

    #[Test]
    public function it_throws_exception_for_unpublished_material()
    {
        $material = CourseMaterial::factory()->create([
            'status' => 'draft',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot extract text from unpublished material');

        $this->extractor->extractText($material);
    }

    #[Test]
    public function it_throws_exception_when_file_not_found()
    {
        $material = CourseMaterial::factory()->create([
            'status' => 'published',
            'file_path' => 'materials/nonexistent.pdf',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Course material file not found');

        $this->extractor->extractText($material);
    }

    #[Test]
    public function it_cleans_text_correctly()
    {
        $reflection = new \ReflectionClass($this->extractor);
        $method = $reflection->getMethod('cleanText');
        $method->setAccessible(true);

        $dirtyText = "Line 1  \n\n\n\n  Line 2   \n   Line 3  ";
        $cleaned = $method->invoke($this->extractor, $dirtyText);

        $this->assertEquals("Line 1\n\nLine 2\nLine 3", $cleaned);
    }

    #[Test]
    public function it_counts_characters_correctly()
    {
        $text = "Hello World";
        $count = $this->extractor->getCharacterCount($text);

        $this->assertEquals(11, $count);
    }

    #[Test]
    public function it_estimates_token_count_correctly()
    {
        $text = str_repeat('a', 400); // 400 characters
        $tokenCount = $this->extractor->estimateTokenCount($text);

        // 400 chars / 4 = 100 tokens
        $this->assertEquals(100, $tokenCount);
    }

    #[Test]
    public function it_handles_unicode_characters()
    {
        $text = "こんにちは世界"; // Japanese characters
        $count = $this->extractor->getCharacterCount($text);

        $this->assertEquals(7, $count);
    }
}
