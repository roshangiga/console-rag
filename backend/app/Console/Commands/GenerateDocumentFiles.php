<?php

namespace App\Console\Commands;

use App\Models\Directory;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateDocumentFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:generate-files {--clean : Clean existing files before generating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dummy files in Laravel storage based on database documents and directory structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting document file generation...');

        // Clean existing files if requested
        if ($this->option('clean')) {
            $this->cleanExistingFiles();
        }

        // Get all documents with their directories
        $documents = Document::with('directory')->get();

        if ($documents->isEmpty()) {
            $this->warn('No documents found in database.');

            return 0;
        }

        $this->info("Found {$documents->count()} documents to process.");

        $progressBar = $this->output->createProgressBar($documents->count());
        $progressBar->start();

        $created = 0;
        $errors = 0;

        foreach ($documents as $document) {
            try {
                $this->generateDocumentFile($document);
                $created++;
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Failed to create file for document {$document->id}: ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Document file generation completed!');
        $this->line("✅ Successfully created: {$created} files");

        if ($errors > 0) {
            $this->line("❌ Errors: {$errors} files");
        }

        return 0;
    }

    /**
     * Clean existing document files
     */
    private function cleanExistingFiles()
    {
        $this->info('Cleaning existing document files...');

        // Clean the documents directory
        if (Storage::disk('local')->exists('documents')) {
            Storage::disk('local')->deleteDirectory('documents');
        }

        Storage::disk('local')->makeDirectory('documents');
        $this->line('✅ Cleaned existing files');
    }

    /**
     * Generate a file for the given document
     */
    private function generateDocumentFile(Document $document)
    {
        // Build the directory path
        $directoryPath = $this->buildDirectoryPath($document->directory);

        // Create the full storage path
        $storagePath = 'documents'.$directoryPath;

        // Ensure directory exists
        if (! Storage::disk('local')->exists($storagePath)) {
            Storage::disk('local')->makeDirectory($storagePath);
        }

        // Generate filename
        $filename = $this->generateFilename($document);
        $fullPath = $storagePath.'/'.$filename;

        // Generate file content based on extension
        $content = $this->generateFileContent($document, $filename);

        // Create the file
        Storage::disk('local')->put($fullPath, $content);

        // Update the document's file_path in database
        $document->update([
            'file_path' => $directoryPath.'/'.$filename,
        ]);
    }

    /**
     * Build the directory path for a document
     */
    private function buildDirectoryPath(?Directory $directory): string
    {
        if (! $directory) {
            return '';
        }

        $path = [];
        $current = $directory;

        while ($current) {
            $path[] = Str::slug($current->name);
            $current = $current->parent;
        }

        return '/'.implode('/', array_reverse($path));
    }

    /**
     * Generate a filename for the document
     */
    private function generateFilename(Document $document): string
    {
        $extension = $this->getExtensionForType($document->type);
        $baseName = Str::slug($document->name);

        // Add version if exists
        if ($document->version) {
            $baseName .= '-v'.$document->version;
        }

        return $baseName.'.'.$extension;
    }

    /**
     * Get file extension based on document type
     */
    private function getExtensionForType(string $type): string
    {
        return match ($type) {
            'QA' => 'pdf',
            'Image' => 'jpg',
            'Process_Chart' => 'pdf',
            'Presentation' => 'pptx',
            'General_Doc' => 'docx',
            default => 'txt'
        };
    }

    /**
     * Generate appropriate content for the file based on type and extension
     */
    private function generateFileContent(Document $document, string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return match ($extension) {
            'pdf' => $this->generatePdfContent($document),
            'docx' => $this->generateDocxContent($document),
            'pptx' => $this->generatePptxContent($document),
            'jpg', 'jpeg', 'png' => $this->generateImageContent($document),
            'txt' => $this->generateTextContent($document),
            default => $this->generateDefaultContent($document)
        };
    }

    /**
     * Generate PDF content (simplified - just text for now)
     */
    private function generatePdfContent(Document $document): string
    {
        return "%PDF-1.4\n".
               "1 0 obj\n".
               "<<\n".
               "/Type /Catalog\n".
               "/Pages 2 0 R\n".
               ">>\n".
               "endobj\n\n".
               "2 0 obj\n".
               "<<\n".
               "/Type /Pages\n".
               "/Kids [3 0 R]\n".
               "/Count 1\n".
               ">>\n".
               "endobj\n\n".
               "3 0 obj\n".
               "<<\n".
               "/Type /Page\n".
               "/Parent 2 0 R\n".
               "/Resources <<\n".
               "/Font <<\n".
               "/F1 4 0 R\n".
               ">>\n".
               ">>\n".
               "/MediaBox [0 0 612 792]\n".
               "/Contents 5 0 R\n".
               ">>\n".
               "endobj\n\n".
               "4 0 obj\n".
               "<<\n".
               "/Type /Font\n".
               "/Subtype /Type1\n".
               "/BaseFont /Times-Roman\n".
               ">>\n".
               "endobj\n\n".
               "5 0 obj\n".
               "<<\n".
               "/Length 44\n".
               ">>\n".
               "stream\n".
               "BT\n".
               "/F1 18 Tf\n".
               "100 700 Td\n".
               "({$document->name}) Tj\n".
               "ET\n".
               "endstream\n".
               "endobj\n\n".
               "xref\n".
               "0 6\n".
               "0000000000 65535 f \n".
               "0000000009 00000 n \n".
               "0000000074 00000 n \n".
               "0000000120 00000 n \n".
               "0000000285 00000 n \n".
               "0000000362 00000 n \n".
               "trailer\n".
               "<<\n".
               "/Size 6\n".
               "/Root 1 0 R\n".
               ">>\n".
               "startxref\n".
               "456\n".
               '%%EOF';
    }

    /**
     * Generate text content for documents
     */
    private function generateTextContent(Document $document): string
    {
        $content = "Document: {$document->name}\n";
        $content .= "Type: {$document->type}\n";
        $content .= "Version: {$document->version}\n";
        $content .= 'Created: '.$document->created_at->format('Y-m-d H:i:s')."\n";

        if ($document->purpose) {
            $content .= "Purpose: {$document->purpose}\n";
        }

        $content .= "\n".str_repeat('=', 50)."\n\n";

        // Generate random content based on document type
        $content .= $this->generateRandomContentByType($document->type);

        return $content;
    }

    /**
     * Generate DOCX content (simplified - just text)
     */
    private function generateDocxContent(Document $document): string
    {
        // This is a simplified DOCX structure
        $content = $this->generateTextContent($document);

        return $content; // In a real implementation, you'd create proper DOCX format
    }

    /**
     * Generate PPTX content (simplified - just text)
     */
    private function generatePptxContent(Document $document): string
    {
        $content = "PowerPoint Presentation: {$document->name}\n";
        $content .= "Slide 1: Introduction\n";
        $content .= "Slide 2: Overview\n";
        $content .= "Slide 3: Details\n";
        $content .= "Slide 4: Conclusion\n";

        return $content; // In a real implementation, you'd create proper PPTX format
    }

    /**
     * Generate dummy image content (1x1 pixel PNG)
     */
    private function generateImageContent(Document $document): string
    {
        // Simple 1x1 pixel PNG
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    }

    /**
     * Generate default content
     */
    private function generateDefaultContent(Document $document): string
    {
        return $this->generateTextContent($document);
    }

    /**
     * Generate random content based on document type
     */
    private function generateRandomContentByType(string $type): string
    {
        return match ($type) {
            'QA' => $this->generateQAContent(),
            'Image' => 'Image Description: This is a sample image file for demonstration purposes.',
            'Process_Chart' => $this->generateProcessChartContent(),
            'Presentation' => $this->generatePresentationContent(),
            'General_Doc' => $this->generateGeneralDocContent(),
            default => 'This is a sample document with random content for testing purposes.'
        };
    }

    private function generateQAContent(): string
    {
        $questions = [
            "Q: What is the primary purpose of this system?\nA: This system is designed to manage and organize documents efficiently.",
            "Q: How do you upload a document?\nA: Click the upload button and select your file from the file browser.",
            "Q: What file formats are supported?\nA: We support PDF, DOCX, XLSX, PPTX, and various image formats.",
            "Q: How is document security handled?\nA: All documents are stored securely with proper access controls.",
            "Q: Can documents be organized in folders?\nA: Yes, documents can be organized in a hierarchical folder structure.",
        ];

        return implode("\n\n", $questions);
    }

    private function generateProcessChartContent(): string
    {
        return "Process Flow:\n".
               "1. Document Upload\n".
               "2. Metadata Extraction\n".
               "3. Content Analysis\n".
               "4. Classification\n".
               "5. Storage\n".
               "6. Indexing\n".
               "7. Search Integration\n\n".
               'This process ensures efficient document management and retrieval.';
    }

    private function generatePresentationContent(): string
    {
        return "Presentation Outline:\n\n".
               "Slide 1: Title - Document Management System\n".
               "Slide 2: Overview - System capabilities and features\n".
               "Slide 3: Architecture - Technical implementation details\n".
               "Slide 4: Benefits - Improved efficiency and organization\n".
               "Slide 5: Demo - Live demonstration of key features\n".
               'Slide 6: Q&A - Questions and discussion';
    }

    private function generateGeneralDocContent(): string
    {
        return "Executive Summary\n\n".
               'This document provides comprehensive information about the document management system implementation. '.
               "The system is designed to handle various document types including PDFs, Word documents, presentations, and images.\n\n".
               "Key Features:\n".
               "- Hierarchical folder organization\n".
               "- Advanced search capabilities\n".
               "- Version control\n".
               "- Secure access controls\n".
               "- Metadata management\n\n".
               'The system supports efficient document workflows and provides users with intuitive tools for managing their content.';
    }
}
