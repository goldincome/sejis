@extends('layouts.admin')

@section('header', 'Application Settings')

@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Application Settings</h1>
         @if($user->isSuperAdmin())
            <a href="{{ route('admin.settings.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create New Setting
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Public</th>
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($settings as $setting)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-medium text-gray-900">{{ $setting->key }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-gray-600 text-xs">
                                @if($setting->is_encrypted)
                                    <span class="italic text-gray-400">[Encrypted]</span>
                                @elseif($setting->type == 'boolean')
                                    <span class="font-bold {{ $setting->value ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $setting->value ? 'True' : 'False' }}
                                    </span>
                                @else
                                    @if(is_array($setting->value) || $setting->value instanceof \Illuminate\Support\Collection)
                                        {{-- It is an array or a collection (which is iterable), so we loop through it --}}
                                        <ul>
                                        @foreach ($setting->value as $key => $item)
                                            <li>
                                                {{ $key }}: {{ is_array($item) ? '...' : $item }}
                                                {{-- You might need to handle nested arrays/objects recursively or display specific properties --}}
                                            </li>
                                        @endforeach
                                        </ul>
                                    @else
                                        {{-- It is not an array or collection, so we just display the value --}}
                                        {{ Str::limit($setting->value, 50) }}
                                    @endif
                                    
                                @endif
                            </code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $setting->group }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $setting->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($setting->is_public)
                                <span class="text-green-600"><i class="fas fa-check-circle"></i> Yes</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle"></i> No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                            <a href="{{ route('admin.settings.show', $setting) }}" class="text-gray-600 hover:text-gray-900 mr-3" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($user->isSuperAdmin())
                                <a href="{{ route('admin.settings.edit', $setting) }}" class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                           
                                <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this setting? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No settings found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settings->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $settings->links() }}
            </div>
        @endif
    </div>
</main>
@endsection