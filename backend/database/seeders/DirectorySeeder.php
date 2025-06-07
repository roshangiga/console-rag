<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create parent directories
        $parentDirectories = [
            ['name' => 'Innovation Projects', 'created_by' => 1],
            ['name' => 'Technical Documentation', 'created_by' => 1],
            ['name' => 'Process Manuals', 'created_by' => 2],
            ['name' => 'Training Materials', 'created_by' => 2],
        ];

        $parentIds = [];
        foreach ($parentDirectories as $dir) {
            $directory = \App\Models\Directory::create([
                'name' => $dir['name'],
                'parent_id' => null,
                'created_by' => $dir['created_by'],
            ]);
            $parentIds[$dir['name']] = $directory->id;
        }

        // Create child directories
        $childDirectories = [
            // Innovation Projects children
            ['name' => 'RAG Systems', 'parent_id' => $parentIds['Innovation Projects'], 'created_by' => 1],
            ['name' => 'AI Research', 'parent_id' => $parentIds['Innovation Projects'], 'created_by' => 2],
            ['name' => 'Mobile Apps', 'parent_id' => $parentIds['Innovation Projects'], 'created_by' => 3],

            // Technical Documentation children
            ['name' => 'API Documentation', 'parent_id' => $parentIds['Technical Documentation'], 'created_by' => 1],
            ['name' => 'System Architecture', 'parent_id' => $parentIds['Technical Documentation'], 'created_by' => 2],

            // Process Manuals children
            ['name' => 'Quality Assurance', 'parent_id' => $parentIds['Process Manuals'], 'created_by' => 2],
            ['name' => 'Development Workflow', 'parent_id' => $parentIds['Process Manuals'], 'created_by' => 3],

            // Training Materials children
            ['name' => 'Onboarding', 'parent_id' => $parentIds['Training Materials'], 'created_by' => 1],
            ['name' => 'Technical Training', 'parent_id' => $parentIds['Training Materials'], 'created_by' => 3],
        ];

        $childIds = [];
        foreach ($childDirectories as $dir) {
            $directory = \App\Models\Directory::create($dir);
            $childIds[$dir['name']] = $directory->id;
        }

        // Create nested children (3rd level)
        $nestedDirectories = [
            ['name' => 'Console Project', 'parent_id' => $childIds['RAG Systems'], 'created_by' => 1],
            ['name' => 'Document Processing', 'parent_id' => $childIds['RAG Systems'], 'created_by' => 2],
            ['name' => 'REST APIs', 'parent_id' => $childIds['API Documentation'], 'created_by' => 1],
            ['name' => 'GraphQL APIs', 'parent_id' => $childIds['API Documentation'], 'created_by' => 2],
            ['name' => 'Testing Procedures', 'parent_id' => $childIds['Quality Assurance'], 'created_by' => 3],
            ['name' => 'Code Review Process', 'parent_id' => $childIds['Development Workflow'], 'created_by' => 1],
        ];

        foreach ($nestedDirectories as $dir) {
            \App\Models\Directory::create($dir);
        }
    }
}
