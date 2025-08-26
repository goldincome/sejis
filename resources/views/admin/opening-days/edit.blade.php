@extends('layouts.admin')
@section('header', 'Opening Time Settings')
@section('content')
<main class="flex-1 p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Work Hours Settings</h1>

    <div class="bg-white shadow-md rounded-lg p-8">
        @if($openingDurations->isEmpty())
             <div class="text-center py-10 text-gray-500">
                <p>Opening hours not set.</p>
                <p class="text-sm">Please run the seeder to populate the opening days.</p>
             </div>
        @else
            <form method="POST" action="{{ route('admin.opening-days.store') }}">
                @csrf

                <div class="font-bold text-gray-800 text-lg mb-4">
                    Work hours
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider w-16"></th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                    Days
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                    Open Time
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                                    Close Time
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($openingDurations as $index => $openingDuration)
                            <tr class="hover:bg-gray-100">
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <input type="checkbox" name="day_of_week[]" value="{{ $openingDuration->id }}" @if(old('day_of_week.'.$index, $openingDuration->status)) checked @endif class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $openingDuration->day_of_week }}</p>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <input type="time" name="start_time[]" value="{{ old('start_time.'.$index, $openingDuration->start_time) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                                    <input type="time" name="end_time[]" value="{{ old('end_time.'.$index, $openingDuration->end_time) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                                </td>
                                <input type="hidden" name="duration_ids[]" value="{{ $openingDuration->id }}" />
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                 @if ($errors->any())
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                        Update
                    </button>
                </div>
            </form>
        @endif
    </div>
</main>
@endsection
