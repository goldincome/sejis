@extends('layouts.admin')

@section('header', 'Edit Setting')

@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Setting: <span class="text-blue-600">{{ $setting->key }}</span></h1>
        <a href="{{ route('admin.settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Back to Settings
        </a>
    </div>

    <div class="w-full bg-white shadow-xl rounded-lg overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.update', $setting) }}" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Key -->
                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700 mb-2">Key</label>
                    <input type="text" id="key" name="key" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 @error('key') border-red-500 @enderror"
                        value="{{ old('key', $setting->key) }}"
                        placeholder="e.g. site.name">
                    @error('key')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Group -->
                <div>
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-2">Group</label>
                    <input type="text" id="group" name="group" required list="group-list"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('group') border-red-500 @enderror"
                        value="{{ old('group', $setting->group) }}"
                        placeholder="e.g. general, mail, social">
                    <datalist id="group-list">
                        @foreach($groups as $group)
                            <option value="{{ $group }}">
                        @endforeach
                    </datalist>
                    @error('group')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" id="type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('type') border-red-500 @enderror"
                    autocomplete="off">
                    <option value="string" {{ old('type', $setting->type) == 'string' ? 'selected' : '' }}>String</option>
                    <option value="boolean" {{ old('type', $setting->type) == 'boolean' ? 'selected' : '' }}>Boolean</option>
                    <option value="integer" {{ old('type', $setting->type) == 'integer' ? 'selected' : '' }}>Integer</option>
                    <option value="json" {{ old('type', $setting->type) == 'json' ? 'selected' : '' }}>JSON</option>
                    <option value="file" {{ old('type', $setting->type) == 'file' ? 'selected' : '' }}>File</option>
                </select>
                @error('type')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Value -->
            <div>
                <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Enabled</label>
                
                @php
                    $value = old('value', $setting->getRawOriginal('value'));
                    if ($setting->is_encrypted) {
                        $value = old('value', $setting->value); // Show decrypted value
                    }
                @endphp

                <!-- Textarea (default) -->
                <textarea id="value_string" name="value" rows="4"
                    class="value-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('value') border-red-500 @enderror">{{ $value }}</textarea>
                
                <!-- Select for Boolean -->
                <select id="value_boolean" name="value_boolean" class="value-input hidden w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="1" {{ $value == '1' ? 'selected' : '' }}>True</option>
                    <option value="0" {{ $value == '0' ? 'selected' : '' }}>False</option>
                </select>

                <!-- Input[number] for Integer -->
                <input type="number" id="value_integer" name="value_integer"
                    class="value-input hidden w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    value="{{ $value }}">
                
                <!-- Input[file] for File -->
                <input type="file" id="value_file" name="value_file"
                    class="value-input hidden w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                
                @if($setting->type == 'file' && $setting->value)
                    <p class="text-xs text-gray-500 mt-1">Current file: {{ $setting->value }}. Uploading a new file will replace it.</p>
                @endif
                @if($setting->is_encrypted)
                    <p class="text-xs text-orange-600 mt-1"><i class="fas fa-lock mr-1"></i> This value is encrypted. Re-saving will re-encrypt it.</p>
                @endif
                @error('value')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Briefly explain what this setting is for.">{{ old('description', $setting->description) }}</textarea>
                @error('description')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Is Public -->
                <div>
                    <label for="is_public" class="block text-sm font-medium text-gray-700 mb-2">Is Public?</label>
                    <select name="is_public" id="is_public" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="0" {{ old('is_public', $setting->is_public) == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_public', $setting->is_public) == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Public settings can be accessed by client-side code.</p>
                </div>

                <!-- Is Encrypted -->
                <div>
                    <label for="is_encrypted" class="block text-sm font-medium text-gray-700 mb-2">Encrypt Value?</label>
                    <select name="is_encrypted" id="is_encrypted" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="0" {{ old('is_encrypted', $setting->is_encrypted) == '0' ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('is_encrypted', $setting->is_encrypted) == '1' ? 'selected' : '' }}>Yes</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Encrypts the value in the database. Use for API keys, etc.</p>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit"
                    class="w-full justify-center inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300">
                    Update Setting
                </button>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueInputs = {
        string: document.getElementById('value_string'), // Also for json
        boolean: document.getElementById('value_boolean'),
        integer: document.getElementById('value_integer'),
        file: document.getElementById('value_file'),
        json: document.getElementById('value_string') // Use textarea for json
    };

    function toggleValueInput(selectedType) {
        // Disable all inputs first
        Object.values(valueInputs).forEach(input => {
            input.classList.add('hidden');
            input.disabled = true;
            if (input.tagName === 'TEXTAREA' || input.tagName === 'INPUT') {
                input.name = ''; // Clear name to avoid submission
            } else if (input.tagName === 'SELECT') {
                input.name = '';
            }
        });

        // Determine which input to show
        let inputToShow;
        if (selectedType === 'boolean') {
            inputToShow = valueInputs.boolean;
            inputToShow.name = 'value'; // Set the correct name for submission
        } else if (selectedType === 'integer') {
            inputToShow = valueInputs.integer;
            inputToShow.name = 'value';
        } else if (selectedType === 'file') {
            inputToShow = valueInputs.file;
            inputToShow.name = 'value';
        } else { // 'string' or 'json'
            inputToShow = valueInputs.string;
            inputToShow.name = 'value';
        }
        
        inputToShow.classList.remove('hidden');
        inputToShow.disabled = false;
    }

    // Initial state
    toggleValueInput(typeSelect.value);

    // Update on change
    typeSelect.addEventListener('change', (e) => {
        toggleValueInput(e.target.value);
    });
});
</script>
@endpush