@extends('layouts.admin')

@section('header', 'View Setting')

@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Setting Details</h1>
        <div>
            <a href="{{ route('admin.settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 mr-2">
                <i class="fas fa-arrow-left mr-2"></i>Back to Settings
            </a>
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.settings.edit', $setting) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                    <i class="fas fa-edit mr-2"></i>Edit Setting
                </a>
            @endif
        </div>
    </div>

    <div class="w-full bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="md:col-span-2 space-y-4">
                    <div class="pb-4 border-b">
                        <label class="text-sm font-medium text-gray-500">Key</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $setting->key }}</p>
                    </div>

                    <div class="pb-4 border-b">
                        <label class="text-sm font-medium text-gray-500">Value</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-lg">
                            @if($setting->is_encrypted)
                                <code class="text-gray-500 italic flex items-center">
                                    <i class="fas fa-lock mr-2 text-orange-500"></i>
                                    [Encrypted Value]
                                </code>
                            @elseif($setting->type == 'boolean')
                                <span class="font-bold text-lg {{ $setting->value ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $setting->value ? 'True' : 'False' }}
                                </span>
                            @elseif($setting->type == 'json')
                                <pre class="text-sm text-gray-800 overflow-x-auto"><code>{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</code></pre>
                            @else
                                <code class="text-gray-800 break-words">{{ $setting->value }}</code>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="text-gray-700 italic mt-1">{{ $setting->description ?: 'No description provided.' }}</p>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="md:col-span-1 space-y-4 md:border-l md:pl-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Group</label>
                        <p class="text-gray-900 font-medium">
                            <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $setting->group }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Type</label>
                        <p class="text-gray-900 font-medium">{{ $setting->type }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Is Public?</label>
                        <p class="text-gray-900 font-medium">
                            @if($setting->is_public)
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Yes</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i> No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Is Encrypted?</label>
                        <p class="text-gray-900 font-medium">
                            @if($setting->is_encrypted)
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Yes</span>
                            @else
                                <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i> No</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Last Updated</label>
                        <p class="text-gray-900 font-medium">{{ $setting->updated_at->format('M d, Y, g:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created At</label>
                        <p class="text-gray-900 font-medium">{{ $setting->created_at->format('M d, Y, g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection