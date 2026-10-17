<?php

namespace App\Services;

use App\Models\CourseMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseMaterialStorageService
{
    public const MAX_FILE_SIZE = 10485760; // 10MB in bytes

    private const PDF_MAGIC_BYTES = '%PDF';
    private const PPT_MAGIC_BYTES = "\xD0\xCF\x11\xE0";
    private const PPTX_MAGIC_BYTES_1 = "PK\x03\x04";
    private const PPTX_MAGIC_BYTES_2 = "PK\x05\x06";

    /**
     * Validate and identify an uploaded file (PDF or PPT/PPTX).
     */
    public function validateAndIdentify(UploadedFile $file): ?array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        try {
            $handle = fopen($file->getRealPath(), 'rb');
            $header = fread($handle, 8);
            fclose($handle);
        } catch (\Exception $e) {
            return null;
        }

        // PDF Check: must start with %PDF
        if (str_starts_with($header, self::PDF_MAGIC_BYTES) && in_array($extension, ['pdf', ''])) {
            return [
                'type' => 'pdf',
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
            ];
        }

        // PPT (legacy binary format)
        if (str_starts_with($header, self::PPT_MAGIC_BYTES) && in_array($extension, ['ppt', 'pps', ''])) {
            return [
                'type' => 'ppt',
                'extension' => 'ppt',
                'mime_type' => 'application/vnd.ms-powerpoint',
            ];
        }

        // PPTX (OpenXML presentation)
        if ((str_starts_with($header, self::PPTX_MAGIC_BYTES_1) || str_starts_with($header, self::PPTX_MAGIC_BYTES_2)) 
            && in_array($extension, ['pptx', 'ppsx', 'ppt', ''])) {
            return [
                'type' => 'ppt',
                'extension' => 'pptx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];
        }

        return null;
    }

    /**
     * Store an uploaded course material in Cloudflare R2 and local storage.
     */
    public function storeMaterialFile(UploadedFile $file, string $extension): array
    {
        $storedFilename = Str::uuid() . '.' . $extension;
        $filePath = 'course-materials/' . $storedFilename;
        $contents = file_get_contents($file->getRealPath());

        $storedOnR2 = false;

        // Try storing on Cloudflare R2
        try {
            $r2Disk = Storage::disk('r2');
            $storedOnR2 = $r2Disk->put($filePath, $contents);
        } catch (\Exception $e) {
            Log::warning('Could not upload course material to Cloudflare R2: ' . $e->getMessage());
        }

        // Always ensure stored on local disk as primary/cache
        try {
            Storage::disk('local')->put($filePath, $contents);
        } catch (\Exception $e) {
            // If local fails and R2 also failed, rethrow
            if (!$storedOnR2) {
                throw new \Exception('Failed to store course material on disk: ' . $e->getMessage());
            }
        }

        return [
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'stored_on_r2' => $storedOnR2,
        ];
    }

    /**
     * Download or stream course material.
     */
    public function download(CourseMaterial $material)
    {
        $filePath = $material->file_path;
        $filename = $material->original_filename;
        $headers = [
            'Content-Type' => $material->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
        ];

        // Check Cloudflare R2 first
        try {
            if (Storage::disk('r2')->exists($filePath)) {
                return Storage::disk('r2')->download($filePath, $filename, $headers);
            }
        } catch (\Exception $e) {
            Log::info('R2 download check failed, falling back to local: ' . $e->getMessage());
        }

        // Fallback to local disk
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, $filename, $headers);
        }

        return null;
    }

    /**
     * Delete material file from all disks.
     */
    public function deleteFile(string $filePath): void
    {
        try {
            if (Storage::disk('r2')->exists($filePath)) {
                Storage::disk('r2')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::warning('Could not delete course material from R2: ' . $e->getMessage());
        }

        try {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::warning('Could not delete course material from local disk: ' . $e->getMessage());
        }
    }

    /**
     * Get binary content of material file.
     */
    public function getFileContent(string $filePath): ?string
    {
        try {
            if (Storage::disk('r2')->exists($filePath)) {
                return Storage::disk('r2')->get($filePath);
            }
        } catch (\Exception $e) {
            // Fallback to local
        }

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->get($filePath);
        }

        return null;
    }
}
