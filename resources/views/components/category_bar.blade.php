@props(['categories', 'selected' => null, 'summary' => null]) 

<div class="space-y-6">
    {{-- Category Buttons --}}
    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
        {{-- "All News" Button --}}
        <a 
            href="{{ route('home') }}" 
            class="px-6 py-2.5 rounded-full font-medium whitespace-nowrap transition-all duration-200 
                   {{ is_null($selected) 
                      ? 'bg-[#41479E] text-white shadow-md' 
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}"
        >
            All News
        </a>

        {{-- Dynamic Loop from Database --}}
        @foreach($categories as $cat)
            <a 
                href="{{ route('home', ['category' => $cat]) }}" 
                class="px-6 py-2.5 rounded-full font-medium whitespace-nowrap transition-all duration-200 
                       {{ $selected === $cat 
                          ? 'bg-[#41479E] text-white shadow-md' 
                          : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' }}"
            >
                {{ ucfirst($cat) }}
            </a>
        @endforeach
    </div>

    {{-- Summary Box --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
            
            <div class="flex-1 space-y-2">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-[#41479E]">✨</span>
                    {{ is_null($selected) ? 'All News' : ucfirst($selected) }} Weekly Summary
                </h3>

                @if($summary)
                    {{-- USE MARKDOWN PARSING HERE --}}
                    <div class="prose prose-sm prose-blue text-gray-600 leading-relaxed max-w-none">
                        {!! Str::markdown($summary) !!}
                    </div>
                @else
                    <p class="text-gray-500 text-sm italic">
                        Get a quick AI-powered recap of the top stories for this week.
                    </p>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="shrink-0 w-full md:w-auto">
                <form method="GET" action="{{ route('home') }}">
                    @if($selected)
                        <input type="hidden" name="category" value="{{ $selected }}">
                    @endif

                    @if($summary)
                        <button 
                            type="submit"
                            name="regenerate_summary"
                            value="1"
                            class="w-full px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Summary
                        </button>
                    @else
                        <button 
                            type="submit"
                            name="generate_summary"
                            value="1"
                            class="w-full px-6 py-2.5 bg-[#41479E] text-white rounded-lg font-medium hover:bg-[#353d82] transition-colors shadow-sm flex items-center justify-center gap-2 text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate Summary
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>