<div class="flex items-center py-2 pl-{{ $directory['level'] * 4 }} {{ $directory['level'] > 0 ? 'border-l border-gray-600 ml-4' : '' }}">
    <div class="flex items-center flex-1">
        <i class="fas fa-folder text-yellow-400 mr-2"></i>
        <span class="text-sm font-medium text-white !text-white">{{ $directory['name'] }}</span>
        
        <div class="ml-auto flex items-center space-x-2">
            <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-900 text-blue-200 rounded-full">
                {{ $directory['document_count'] }} docs
            </span>
            
            @if($directory['children_count'] > 0)
                <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded-full">
                    {{ $directory['children_count'] }} folders
                </span>
            @endif
            
            @if($directory['total_documents'] !== $directory['document_count'])
                <span class="inline-flex px-2 py-1 text-xs font-medium bg-green-900 text-green-200 rounded-full">
                    {{ $directory['total_documents'] }} total
                </span>
            @endif
        </div>
    </div>
</div>

@if(count($directory['children']) > 0)
    @foreach($directory['children'] as $child)
        @include('partials.directory-tree-item', ['directory' => $child])
    @endforeach
@endif