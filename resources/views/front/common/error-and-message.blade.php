<div class="container mx-auto px-4 sm:px-6 lg:px-8 my-4">
    {{-- Container to center and provide padding for the alerts --}}

    @if($errors->all())
        @foreach($errors->all() as $message)
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md mb-4" role="alert">
                <div class="flex">
                    <div class="py-1"><i class="fas fa-times-circle mr-3 text-red-500"></i></div>
                    <div>
                        <p class="font-bold">Error</p>
                        <p class="text-sm">{{ $message }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" class="text-red-500 hover:text-red-700 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.style.display='none';" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

    @elseif(session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md mb-4" role="alert">
            <div class="flex">
                <div class="py-1"><i class="fas fa-check-circle mr-3 text-green-500"></i></div>
                <div>
                    <p class="font-bold">Success</p>
                    <p class="text-sm">{!! session()->get('success') !!}</p>
                </div>
                <div class="ml-auto pl-3">
                     <button type="button" class="text-green-500 hover:text-green-700 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.style.display='none';" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>

    @elseif(session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md mb-4" role="alert">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-triangle mr-3 text-red-500"></i></div>
                <div>
                    <p class="font-bold">Alert</p>
                    <p class="text-sm">{{ session()->get('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                     <button type="button" class="text-red-500 hover:text-red-700 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.style.display='none';" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
