<?php

namespace App\Console\Commands;

use App\Models\Directory;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixStoragePathConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:fix-paths {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix storage path consistency by moving files to standardized lowercase-hyphenated paths';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->info('🔧 FIXING STORAGE PATHS - Files will be moved');
        }

        $this->line('');

        // Get all documents with file paths
        $documents = Document::whereNotNull('file_path')->with('directory')->get();

        $movedCount = 0;
        $errorCount = 0;

        foreach ($documents as $document) {
            try {
                $currentPath = 'documents'.$document->file_path;
                $expectedPath = $this->getExpectedFilePath($document);

                // Skip if paths are already consistent
                if ($currentPath === $expectedPath) {
                    continue;
                }

                $this->line("Document ID {$document->id}: {$document->name}");
                $this->line("  Current:  {$currentPath}");
                $this->line("  Expected: {$expectedPath}");

                // Check if current file exists
                if (! Storage::disk('local')->exists($currentPath)) {
                    $this->error('  ❌ Current file does not exist!');
                    $errorCount++;

                    continue;
                }

                if (! $dryRun) {
                    // Create directory if needed
                    $expectedDir = dirname($expectedPath);
                    if (! Storage::disk('local')->exists($expectedDir)) {
                        Storage::disk('local')->makeDirectory($expectedDir);
                        $this->info("  📁 Created directory: {$expectedDir}");
                    }

                    // Move the file
                    if (Storage::disk('local')->move($currentPath, $expectedPath)) {
                        // Update database
                        $newDbPath = str_replace('documents', '', $expectedPath);
                        $document->update(['file_path' => $newDbPath]);

                        $this->info('  ✅ Moved successfully');
                        $movedCount++;

                        // Clean up empty directories
                        $this->cleanupEmptyDirectories(dirname($currentPath));
                    } else {
                        $this->error('  ❌ Failed to move file');
                        $errorCount++;
                    }
                } else {
                    $this->comment('  📋 Would be moved');
                    $movedCount++;
                }

                $this->line('');

            } catch (\Exception $e) {
                $this->error("Error processing document {$document->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        // Summary
        $this->line('=================================');
        if ($dryRun) {
            $this->info('📊 DRY RUN SUMMARY:');
            $this->info("Files that would be moved: {$movedCount}");
        } else {
            $this->info('📊 MIGRATION SUMMARY:');
            $this->info("Files moved successfully: {$movedCount}");
        }

        if ($errorCount > 0) {
            $this->error("Errors encountered: {$errorCount}");
        }

        if ($dryRun && $movedCount > 0) {
            $this->line('');
            $this->comment('Run without --dry-run to actually perform the migration:');
            $this->comment('php artisan storage:fix-paths');
        }

        return 0;
    }

    /**
     * Get the expected file path for a document
     */
    private function getExpectedFilePath(Document $document)
    {
        $directoryPath = $this->getExpectedDirectoryPath($document->directory);
        $filename = basename($document->file_path);

        return 'documents'.$directoryPath.'/'.$filename;
    }

    /**
     * Get the expected directory path (sanitized)
     */
    private function getExpectedDirectoryPath($directory)
    {
        if (! $directory) {
            return '';
        }

        $path = '/'.$this->sanitizeDirectoryName($directory->name);
        $parent = $directory->parent;

        while ($parent) {
            $path = '/'.$this->sanitizeDirectoryName($parent->name).$path;
            $parent = $parent->parent;
        }

        return $path;
    }

    /**
     * Sanitize directory name for file system storage
     */
    private function sanitizeDirectoryName($name)
    {
        return strtolower(preg_replace('/\s+/', '-', trim($name)));
    }

    /**
     * Clean up empty directories after moving files
     */
    private function cleanupEmptyDirectories($directory)
    {
        if (! Storage::disk('local')->exists($directory)) {
            return;
        }

        $files = Storage::disk('local')->files($directory);
        $subdirs = Storage::disk('local')->directories($directory);

        // If directory is empty, delete it
        if (empty($files) && empty($subdirs)) {
            Storage::disk('local')->deleteDirectory($directory);
            $this->comment("  🗑️  Removed empty directory: {$directory}");

            // Recursively check parent directories
            $parentDir = dirname($directory);
            if ($parentDir !== $directory && $parentDir !== 'documents') {
                $this->cleanupEmptyDirectories($parentDir);
            }
        }
    }
}
