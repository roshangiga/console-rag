<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'name' => 'RAG System Architecture',
                'directory_id' => 10, // Console Project
                'file_path' => 'documents/rag-architecture.pdf',
                'version' => '1.0',
                'type' => 'Presentation',
                'purpose' => 'Technical overview of RAG system architecture and components',
                'metadata' => ['author' => 'Innovation Team', 'confidentiality' => 'internal'],
                'status' => 'SUCCESS',
                'created_by' => 1,
                'tags' => ['Internal', 'Enterprise'],
            ],
            [
                'name' => 'API Endpoints Documentation',
                'directory_id' => 12, // REST APIs
                'file_path' => 'documents/api-docs.docx',
                'version' => '2.1',
                'type' => 'General_Doc',
                'purpose' => 'Complete documentation of all REST API endpoints',
                'metadata' => ['format' => 'OpenAPI 3.0', 'last_updated' => '2025-06-01'],
                'status' => 'SUCCESS',
                'created_by' => 2,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'User Interface Mockups',
                'directory_id' => 10, // Console Project
                'file_path' => 'documents/ui-mockups.png',
                'version' => '1.5',
                'type' => 'Image',
                'purpose' => 'Design mockups for the console RAG interface',
                'metadata' => ['designer' => 'UX Team', 'tool' => 'Figma'],
                'status' => 'UPDATING',
                'created_by' => 3,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'QA Testing Checklist',
                'directory_id' => 14, // Testing Procedures
                'file_path' => 'documents/qa-checklist.xlsx',
                'version' => '3.0',
                'type' => 'QA',
                'purpose' => 'Comprehensive testing checklist for quality assurance',
                'metadata' => ['template_version' => '3.0', 'coverage' => 'full'],
                'status' => 'SUCCESS',
                'created_by' => 2,
                'tags' => ['Internal', 'Enterprise'],
            ],
            [
                'name' => 'Development Workflow Chart',
                'directory_id' => 15, // Code Review Process
                'file_path' => 'documents/workflow-chart.pdf',
                'version' => '1.2',
                'type' => 'Process_Chart',
                'purpose' => 'Visual representation of development and code review workflow',
                'metadata' => ['chart_type' => 'flowchart', 'last_review' => '2025-05-15'],
                'status' => 'SUCCESS',
                'created_by' => 1,
                'tags' => ['Public'],
            ],
            [
                'name' => 'Employee Onboarding Guide',
                'directory_id' => 8, // Onboarding
                'file_path' => 'documents/onboarding-guide.pdf',
                'version' => '4.0',
                'type' => 'General_Doc',
                'purpose' => 'Complete guide for new employee onboarding process',
                'metadata' => ['department' => 'HR', 'validity' => '1 year'],
                'status' => 'SUCCESS',
                'created_by' => 1,
                'tags' => ['Public'],
            ],
            [
                'name' => 'AI Research Findings',
                'directory_id' => 5, // AI Research
                'file_path' => 'documents/ai-research.pptx',
                'version' => '1.0',
                'type' => 'Presentation',
                'purpose' => 'Latest findings from AI research initiatives',
                'metadata' => ['research_period' => 'Q1 2025', 'status' => 'preliminary'],
                'status' => 'FAILED',
                'created_by' => 2,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'Mobile App Screenshots',
                'directory_id' => 6, // Mobile Apps
                'file_path' => 'documents/app-screenshots.jpg',
                'version' => '2.3',
                'type' => 'Image',
                'purpose' => 'Current mobile application interface screenshots',
                'metadata' => ['platform' => 'iOS/Android', 'resolution' => '1080p'],
                'status' => 'SUCCESS',
                'created_by' => 3,
                'tags' => ['Public'],
            ],
            [
                'name' => 'System Architecture Diagram',
                'directory_id' => 7, // System Architecture
                'file_path' => 'documents/system-arch.png',
                'version' => '3.1',
                'type' => 'Process_Chart',
                'purpose' => 'Overall system architecture and component relationships',
                'metadata' => ['diagram_tool' => 'Draw.io', 'complexity' => 'high'],
                'status' => 'UPDATING',
                'created_by' => 1,
                'tags' => ['Internal', 'Enterprise'],
            ],
            [
                'name' => 'Technical Training Materials',
                'directory_id' => 9, // Technical Training
                'file_path' => 'documents/tech-training.zip',
                'version' => '5.2',
                'type' => 'General_Doc',
                'purpose' => 'Comprehensive technical training resources and exercises',
                'metadata' => ['format' => 'mixed', 'duration' => '40 hours'],
                'status' => 'SUCCESS',
                'created_by' => 3,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'Document Processing Logic',
                'directory_id' => 11, // Document Processing
                'file_path' => 'documents/doc-processing.pdf',
                'version' => '1.8',
                'type' => 'Process_Chart',
                'purpose' => 'Flowchart showing document processing pipeline',
                'metadata' => ['pipeline_stages' => 5, 'performance' => 'optimized'],
                'status' => 'SUCCESS',
                'created_by' => 2,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'Quality Metrics Report',
                'directory_id' => 8, // Quality Assurance
                'file_path' => 'documents/quality-metrics.xlsx',
                'version' => '2.4',
                'type' => 'QA',
                'purpose' => 'Monthly quality metrics and performance indicators',
                'metadata' => ['reporting_period' => 'May 2025', 'kpis' => 12],
                'status' => 'SUCCESS',
                'created_by' => 2,
                'tags' => ['Enterprise'],
            ],
            [
                'name' => 'GraphQL Schema Documentation',
                'directory_id' => 13, // GraphQL APIs
                'file_path' => 'documents/graphql-schema.md',
                'version' => '1.3',
                'type' => 'General_Doc',
                'purpose' => 'Complete GraphQL schema definition and usage examples',
                'metadata' => ['schema_version' => '1.3', 'endpoints' => 25],
                'status' => 'UPDATING',
                'created_by' => 1,
                'tags' => ['Internal'],
            ],
            [
                'name' => 'Innovation Strategy Presentation',
                'directory_id' => 1, // Innovation Projects
                'file_path' => 'documents/innovation-strategy.pptx',
                'version' => '2.0',
                'type' => 'Presentation',
                'purpose' => 'Strategic roadmap for innovation initiatives',
                'metadata' => ['board_approved' => true, 'fiscal_year' => '2025'],
                'status' => 'SUCCESS',
                'created_by' => 1,
                'tags' => ['Enterprise'],
            ],
            [
                'name' => 'Bug Tracking QA Process',
                'directory_id' => 14, // Testing Procedures
                'file_path' => 'documents/bug-tracking.docx',
                'version' => '1.7',
                'type' => 'QA',
                'purpose' => 'Standard procedures for bug identification and tracking',
                'metadata' => ['tool' => 'Jira', 'severity_levels' => 4],
                'status' => 'FAILED',
                'created_by' => 3,
                'tags' => ['Internal'],
            ],
        ];

        foreach ($documents as $docData) {
            $tags = $docData['tags'];
            unset($docData['tags']);

            $document = \App\Models\Document::create($docData);

            // Add tags
            foreach ($tags as $tag) {
                \App\Models\DocumentTag::create([
                    'document_id' => $document->id,
                    'tag_name' => $tag,
                ]);
            }
        }
    }
}
