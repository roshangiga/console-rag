<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Console RAG - Laravel Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 min-h-screen text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Console RAG Dashboard</h1>
                    <p class="text-gray-300 mt-1">Laravel Backend - Document Management System</p>
                    <p class="text-sm text-gray-400 mt-1">Mauritius Telecom Innovation Department</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-400">Server Time</div>
                    <div class="text-lg font-semibold text-white">{{ now()->format('Y-m-d H:i:s') }}</div>
                    <div class="text-sm text-gray-400">{{ config('app.env') }} Environment</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-folder text-blue-400 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-white">{{ $stats['total_directories'] }}</div>
                        <div class="text-sm text-gray-300">Total Directories</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt text-green-400 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-white">{{ $stats['total_documents'] }}</div>
                        <div class="text-sm text-gray-300">Total Documents</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-purple-400 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-white">{{ $stats['total_users'] }}</div>
                        <div class="text-sm text-gray-300">Total Users</div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-orange-400 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-white">{{ $stats['total_documents'] > 0 ? round($stats['total_documents'] / max($stats['total_directories'], 1), 1) : 0 }}</div>
                        <div class="text-sm text-gray-300">Docs per Directory</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Directory Tree Structure -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-sitemap mr-2 text-blue-400"></i>
                            Directory Structure
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @if(count($directoryTree) > 0)
                                @foreach($directoryTree as $directory)
                                    @include('partials.directory-tree-item', ['directory' => $directory])
                                @endforeach
                            @else
                                <div class="text-center py-8 text-gray-400">
                                    <i class="fas fa-folder-open text-4xl mb-2"></i>
                                    <p>No directories found</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700 mt-8">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-clock mr-2 text-green-400"></i>
                            Recent Activity
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Directory</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                @foreach($recentActivity as $activity)
                                <tr class="hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-gray-400 mr-2"></i>
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ $activity['name'] }}</div>
                                                <div class="text-sm text-gray-400">#{{ $activity['id'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-900 text-blue-200 rounded-full">
                                            {{ str_replace('_', ' ', $activity['type']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            @if($activity['status'] === 'SUCCESS') bg-green-900 text-green-200
                                            @elseif($activity['status'] === 'FAILED') bg-red-900 text-red-200
                                            @else bg-yellow-900 text-yellow-200 @endif">
                                            {{ $activity['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white">{{ $activity['directory'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $activity['file_size_mb'] }} MB</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ $activity['created_at']->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Document Types Chart -->
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-chart-pie mr-2 text-purple-400"></i>
                            Documents by Type
                        </h3>
                    </div>
                    <div class="p-6">
                        @foreach($stats['documents_by_type'] as $typeData)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-300">{{ str_replace('_', ' ', $typeData->type) }}</span>
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-600 rounded-full h-2 mr-2">
                                    <div class="bg-blue-400 h-2 rounded-full" style="width: {{ $stats['total_documents'] > 0 ? ($typeData->count / $stats['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-white">{{ $typeData->count }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Document Status -->
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-tasks mr-2 text-green-400"></i>
                            Document Status
                        </h3>
                    </div>
                    <div class="p-6">
                        @foreach($stats['documents_by_status'] as $statusData)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-300">{{ $statusData->status }}</span>
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-600 rounded-full h-2 mr-2">
                                    <div class="h-2 rounded-full 
                                        @if($statusData->status === 'SUCCESS') bg-green-400
                                        @elseif($statusData->status === 'FAILED') bg-red-400
                                        @else bg-yellow-400 @endif" 
                                        style="width: {{ $stats['total_documents'] > 0 ? ($statusData->count / $stats['total_documents']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-white">{{ $statusData->count }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-link mr-2 text-orange-400"></i>
                            Quick Links
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="/api/documentation" target="_blank" class="flex items-center p-3 text-sm text-gray-300 rounded-lg border border-gray-600 hover:bg-gray-700 transition-colors">
                            <i class="fas fa-book mr-3 text-blue-400"></i>
                            API Documentation
                            <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                        </a>
                        
                        <a href="http://localhost:8080" target="_blank" class="flex items-center p-3 text-sm text-gray-300 rounded-lg border border-gray-600 hover:bg-gray-700 transition-colors">
                            <i class="fas fa-database mr-3 text-green-400"></i>
                            phpMyAdmin
                            <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                        </a>
                        
                        <a href="/api/directories" target="_blank" class="flex items-center p-3 text-sm text-gray-300 rounded-lg border border-gray-600 hover:bg-gray-700 transition-colors">
                            <i class="fas fa-folder mr-3 text-purple-400"></i>
                            Directories API
                            <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                        </a>
                        
                        <a href="/api/documents" target="_blank" class="flex items-center p-3 text-sm text-gray-300 rounded-lg border border-gray-600 hover:bg-gray-700 transition-colors">
                            <i class="fas fa-file-alt mr-3 text-orange-400"></i>
                            Documents API
                            <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                        </a>

                        <a href="http://localhost:3000" target="_blank" class="flex items-center p-3 text-sm text-gray-300 rounded-lg border border-gray-600 hover:bg-gray-700 transition-colors">
                            <i class="fab fa-vuejs mr-3 text-green-400"></i>
                            Frontend (Vue.js)
                            <i class="fas fa-external-link-alt ml-auto text-gray-500"></i>
                        </a>
                    </div>
                </div>

                <!-- System Info -->
                <div class="bg-gray-800 rounded-lg shadow-sm border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-server mr-2 text-gray-400"></i>
                            System Information
                        </h3>
                    </div>
                    <div class="p-6 text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-300">Laravel Version:</span>
                            <span class="font-medium text-white">{{ app()->version() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">PHP Version:</span>
                            <span class="font-medium text-white">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Environment:</span>
                            <span class="font-medium capitalize text-white">{{ config('app.env') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Debug Mode:</span>
                            <span class="font-medium {{ config('app.debug') ? 'text-red-400' : 'text-green-400' }}">
                                {{ config('app.debug') ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-300">Database:</span>
                            <span class="font-medium text-white">{{ config('database.default') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>