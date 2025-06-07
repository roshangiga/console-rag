<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateDocumentSizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:update-sizes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update file sizes for all documents based on actual file sizes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating document file sizes...');

        $documents = Document::whereNotNull('file_path')->get();

        if ($documents->isEmpty()) {
            $this->warn('No documents with file paths found.');

            return 0;
        }

        $progressBar = $this->output->createProgressBar($documents->count());
        $progressBar->start();

        $updated = 0;
        $errors = 0;

        foreach ($documents as $document) {
            try {
                $fullPath = 'documents'.$document->file_path;

                if (Storage::disk('local')->exists($fullPath)) {
                    $size = Storage::disk('local')->size($fullPath);
                    $document->update(['file_size' => $size]);
                    $updated++;
                } else {
                    $this->newLine();
                    $this->warn("File not found: {$fullPath}");
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Failed to update document {$document->id}: ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('File size update completed!');
        $this->line("✅ Successfully updated: {$updated} documents");

        if ($errors > 0) {
            $this->line("❌ Errors: {$errors} documents");
        }

        return 0;
    }
}
