@extends('layouts.admin')
@section('header', 'Holiday Schedule')
@section('content')
<main class="flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Public Holidays/Off Dates</h1>
        <a href="{{ route('admin.off-dates.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
            <i class="fas fa-plus mr-2"></i>Add Public Holiday/Off Date
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                            From
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                            To
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left text-xs font-semibold uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidaySchedules as $holiday)
                    <tr class="hover:bg-gray-100">
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $holiday->title }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $holiday->date_from->format('M d, Y') }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $holiday->date_to->format('M d, Y') }}</p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                             @if($holiday->status)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 bg-white text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.off-dates.show', $holiday) }}" class="text-blue-500 hover:text-blue-700" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.off-dates.edit', $holiday) }}" class="text-yellow-500 hover:text-yellow-700" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.off-dates.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this holiday?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">
                            No public holidays found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $holidaySchedules->links() }}
        </div>
    </div>
</main>
@endsection
