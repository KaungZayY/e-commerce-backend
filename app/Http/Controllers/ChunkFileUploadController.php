<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChunkFileUploadController extends Controller
{
    public function initChunkedUpload(Request $request)
    {
        try {
            $request->validate([
                'fileName' => 'required|string',
                'fileType' => 'required|string',
                'fileSize' => 'required|numeric',
                'totalChunks' => 'required|integer',
                'for' => 'required|string'
            ]);

            // Validate file type
            // $allowedTypes = ['video/mp4', 'image/webp'];
            // if (!in_array($request->fileType, $allowedTypes)) {
            //     return response()->json(['error' => 'Invalid file type. Only MP4 & WebP files are supported!'], 422);
            // }

            // Validate file size (50MB max)
            if ($request->fileSize > 50 * 1024 * 1024) {
                return response()->json(['error' => 'File size exceeds the maximum limit of 50MB.'], 422);
            }

            // Create a temporary directory for chunks
            $fileId = uniqid('upload_');
            $tempDir = storage_path('app/temp/chunks/' . $fileId);

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // Store upload metadata
            $metadata = [
                'fileName' => $request->fileName,
                'fileType' => $request->fileType,
                'fileSize' => $request->fileSize,
                'totalChunks' => $request->totalChunks,
                'uploadedChunks' => 0,
                'for' => $request->for,
                'createdAt' => now()->timestamp
            ];

            File::put($tempDir . '/metadata.json', json_encode($metadata));

            return response()->json([
                'fileId' => $fileId,
                'message' => 'Chunked upload initialized'
            ]);
        } catch (\Exception $e) {
            Log::error('Chunked upload initialization error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle upload of a single chunk
     */
    public function uploadChunk(Request $request)
    {
        try {
            $request->validate([
                'chunk' => 'required|file',
                'chunkIndex' => 'required|integer',
                'fileId' => 'required|string',
                'totalChunks' => 'required|integer',
                'for' => 'required|string'
            ]);

            $fileId = $request->fileId;
            $chunkIndex = $request->chunkIndex;
            $tempDir = storage_path('app/temp/chunks/' . $fileId);

            // Check if upload session exists
            if (!File::exists($tempDir . '/metadata.json')) {
                return response()->json(['error' => 'Upload session not found or expired'], 404);
            }

            // Store the chunk
            $chunk = $request->file('chunk');
            $chunkPath = $tempDir . '/chunk_' . $chunkIndex;
            $chunk->move(dirname($chunkPath), basename($chunkPath));

            // Update metadata
            $metadata = json_decode(File::get($tempDir . '/metadata.json'), true);
            $metadata['uploadedChunks'] += 1;
            File::put($tempDir . '/metadata.json', json_encode($metadata));

            return response()->json([
                'message' => 'Chunk uploaded successfully',
                'chunkIndex' => $chunkIndex,
                'received' => $metadata['uploadedChunks'],
                'total' => $metadata['totalChunks']
            ]);
        } catch (\Exception $e) {
            Log::error('Chunk upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Complete a chunked upload by merging all chunks
     */
    public function completeChunkedUpload(Request $request)
    {
        try {
            $request->validate([
                'fileId' => 'required|string',
                'totalChunks' => 'required|integer',
                'for' => 'required|string'
            ]);

            $fileId = $request->fileId;
            $tempDir = storage_path('app/temp/chunks/' . $fileId);

            // Check if upload session exists
            if (!File::exists($tempDir . '/metadata.json')) {
                return response()->json(['error' => 'Upload session not found or expired'], 404);
            }

            // Read metadata
            $metadata = json_decode(File::get($tempDir . '/metadata.json'), true);

            // Ensure all chunks are received
            if ($metadata['uploadedChunks'] != $metadata['totalChunks']) {
                return response()->json([
                    'error' => 'Not all chunks received',
                    'received' => $metadata['uploadedChunks'],
                    'expected' => $metadata['totalChunks']
                ], 400);
            }

            // Get just the name without extension
            $baseName = pathinfo($metadata['fileName'], PATHINFO_FILENAME);

            // Get file extension from the original file type
            $extension = pathinfo($metadata['fileName'], PATHINFO_EXTENSION);

            // Generate final filename
            // $filename = uniqid() . '.' . $extension;
            $filename = $baseName . '_' . uniqid() . '.' . $extension;

            // Determine directory based on the 'for' parameter
            $dir = $this->getDirectoryForFileType($metadata['for']);
            // Create the folder path for local disk
            $folderPath = $dir; // This is the relative path used by Storage

            // Merge chunks into a single binary string
            $mergedContent = '';
            for ($i = 0; $i < $metadata['totalChunks']; $i++) {
                $chunkPath = $tempDir . '/chunk_' . $i;
                $mergedContent .= file_get_contents($chunkPath);
                unlink($chunkPath); // delete chunk
            }

            // Store merged file using Storage
            Storage::disk('local')->put($folderPath . '/' . $filename, $mergedContent);

            // Cleanup
            File::deleteDirectory($tempDir);

            // Return response
            return response()->json([
                'path' => $folderPath . '/' . $filename,
                'message' => 'Upload complete'
            ]);
        } catch (\Exception $e) {
            Log::error('Complete chunked upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Your existing helper method
    private function getDirectoryForFileType($for)
    {
        $folderMap = [
            'product_images' => 'files/products/images',
        ];
    
        return $folderMap[$for] ?? 'files/uploads/';
    }
}
