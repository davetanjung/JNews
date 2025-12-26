
<x-layout>
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    
    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Summary History</h1>
                <p class="mt-2 text-gray-600">A personal archive of news topics you've explored.</p>
            </div>
            
            {{-- Optional: "Back to News" Button --}}
            <a href="{{ route('home') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-[#41479E] transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to News
            </a>
        </div>
    </div>

    {{-- Content Grid --}}
    <div class="max-w-7xl mx-auto">
        @if($summaries->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($summaries as $summary)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 overflow-hidden flex flex-col h-full group">
                        
                        {{-- Card Header: Date & ID --}}
                        <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                            <span class="text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                {{ $summary->created_at->format('M d, Y • h:i A') }}
                            </span>
                            <div class="h-2 w-2 rounded-full bg-[#41479E]"></div>
                        </div>

                        {{-- Card Body: The Summary Content --}}
                        <div class="p-6 flex-1">
                            <div class="prose prose-sm prose-indigo text-gray-600 line-clamp-[10]">
                                {{-- We render raw HTML because your Gemini summary might contain <ol> tags --}}
                                {!! $summary->summary_content !!}
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 mt-auto">
                            <button onclick="openModal('summary-modal-{{ $summary->id }}')" 
                                    class="text-[#41479E] text-sm font-medium hover:text-[#2d3275] transition-colors flex items-center group-hover:translate-x-1 duration-200">
                                Read Full Summary
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal for Full View (Hidden by default) --}}
                    <div id="summary-modal-{{ $summary->id }}" 
                         class="fixed inset-0 z-[5000] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        
                        {{-- Backdrop --}}
                        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal('summary-modal-{{ $summary->id }}')"></div>

                        {{-- Modal Panel --}}
                        <div class="fixed inset-0 z-10 overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border-t-4 border-[#41479E]">
                                    
                                    {{-- Modal Header --}}
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                            Summary Details
                                        </h3>
                                        <button type="button" onclick="closeModal('summary-modal-{{ $summary->id }}')" class="text-gray-400 hover:text-gray-500">
                                            <span class="sr-only">Close</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>

                                    {{-- Modal Content --}}
                                    <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                                        <div class="prose prose-indigo max-w-none text-gray-700">
                                            {!! $summary->summary_content !!}
                                        </div>
                                        <div class="mt-6 text-xs text-gray-400 border-t pt-4">
                                            Generated on {{ $summary->created_at->format('F j, Y \a\t g:i A') }}
                                        </div>
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button type="button" onclick="closeModal('summary-modal-{{ $summary->id }}')" 
                                                class="inline-flex w-full justify-center rounded-md bg-[#41479E] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#343985] sm:ml-3 sm:w-auto transition-colors">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $summaries->links() }} 
                {{-- Make sure you have Tailwind pagination published or it might look plain --}}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20 bg-white rounded-xl border border-gray-200 border-dashed">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No summaries yet</h3>
                <p class="mt-1 text-sm text-gray-500">Generate a summary from the News page to see it here.</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="inline-flex items-center rounded-md bg-[#41479E] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#343985]">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        Go to News
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
</x-layout>

{{-- Simple Script for Modals --}}
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
</script>