@props(['selected' => null, 'summary' => null]) 

<div class="space-y-6">
    {{-- Category Buttons --}}
    <div class="flex gap-3 overflow-x-auto pb-2">
        @php
            $categories = [
                'general' => 'General',
                'technology' => 'Technology',
                'business' => 'Business',
                'sports' => 'Sports',
                'entertainment' => 'Entertainment'
            ];
        @endphp
        
        <a 
            href="{{ route('home') }}" 
            class="px-6 py-2.5 rounded-full font-medium whitespace-nowrap transition-all duration-200 
                   {{ is_null($selected) 
                      ? 'bg-[#41479E] text-white shadow-md' 
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}"
        >
            All News
        </a>

        @foreach($categories as $value => $label)
            <a 
                href="{{ route('home', ['category' => $value]) }}" 
                class="px-6 py-2.5 rounded-full font-medium whitespace-nowrap transition-all duration-200 
                       {{ $selected === $value 
                          ? 'bg-[#41479E] text-white shadow-md' 
                          : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Summary Box --}}
    <div class="bg-white rounded-xl border-2 border-gray-200 p-8 shadow-lg">
        <div class="space-y-4">
            <h3 class="text-xl font-bold text-gray-900">
                {{ is_null($selected) ? 'All News' : ucfirst($selected) }} Summary
            </h3>
            
            @if($summary)
                <div class="text-gray-700 leading-relaxed bg-blue-50 p-4 rounded-lg border border-blue-200">
                    {{ $summary }}
                </div>
                
                <form method="GET" action="{{ route('home') }}">
                    @if($selected)
                        <input type="hidden" name="category" value="{{ $selected }}">
                    @endif
                    <button 
                        type="submit"
                        name="regenerate_summary"
                        value="1"
                        class="w-full sm:w-auto px-8 py-3 bg-[#41479E] text-white rounded-lg font-semibold hover:bg-[#353d82] transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Regenerate Summary
                    </button>
                </form>
            @else
                <div class="text-gray-500 italic bg-gray-50 p-4 rounded-lg border border-gray-200">
                    Click the button below to generate a summary of {{ is_null($selected) ? 'all news' : $selected . ' news' }} articles...
                </div>
                
                <form method="GET" action="{{ route('home') }}">
                    @if($selected)
                        <input type="hidden" name="category" value="{{ $selected }}">
                    @endif
                    <button 
                        type="submit"
                        name="generate_summary"
                        value="1"
                        class="w-full sm:w-auto px-8 py-3 bg-[#41479E] text-white rounded-lg font-semibold hover:bg-[#353d82] transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Generate Summary
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>