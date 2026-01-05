<x-layout>
    {{-- Full Page Loading Overlay --}}
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center space-y-4 animate-scale-in">
            <svg class="animate-spin h-12 w-12 text-[#41479E]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-800">Processing Request...</p>
                <p class="text-sm text-gray-500 mt-1">This may take a few moments</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        {{-- Header with fade-in animation --}}
        <div class="mb-8 text-center animate-fade-in">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Latest Articles</h1>
            <p class="text-gray-600 text-lg">Discover our curated collection of stories and insights</p>
        </div>

        {{-- Search Bar with slide-in animation --}}
        <div class="mb-6 max-w-2xl mx-auto animate-slide-in-down" style="animation-delay: 0.1s">
            <form action="{{ route('home') }}" method="GET" class="relative">

                {{-- PRO TIP: Hidden Input to keep the category selected when searching --}}
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div class="flex gap-2">
                    <div class="relative flex-grow">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                            placeholder="Search articles..."
                            class="w-full px-4 py-3 pl-12 pr-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all hover:scale-105 active:scale-95">
                        Search
                    </button>

                    {{-- Clear Button --}}
                    @if ($search || request('category'))
                        <a href="{{ route('home') }}"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium flex items-center transition-all hover:scale-105 active:scale-95">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Category Bar with animation --}}
        <div class="mb-8 flex justify-center animate-slide-in-down" style="animation-delay: 0.2s">
            <x-category_bar :selected="$activeCategory" :subcategories="$subcategories" :selectedSubcategory="$activeSubcategory" :summary="$summary" />
        </div>

        {{-- Results Info --}}
        @if ($search || request('category'))
            <div class="mb-6 text-center animate-fade-in" style="animation-delay: 0.3s">
                <p class="text-gray-600 text-sm">
                    Showing results for
                    @if (request('category'))
                        Category: <span class="font-semibold text-blue-600">{{ ucfirst(request('category')) }}</span>
                    @endif
                    @if ($search)
                        @if (request('category'))
                            &
                        @endif
                        Search: <span class="font-semibold text-blue-600">"{{ $search }}"</span>
                    @endif
                    ({{ $articles->total() }} results)
                </p>
            </div>
        @endif

        {{-- Articles Grid with staggered animation --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($articles as $index => $article)
                <article
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col group animate-scale-in"
                    style="animation-delay: {{ 0.05 * ($index % 12) }}s">
                    <div class="h-48 bg-gray-200 overflow-hidden relative">
                        @if ($article->image)
                            <img src="{{ $article->image }}" alt="{{ $article->title }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <div
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-purple-500">
                                <i class="fas fa-newspaper text-white text-4xl opacity-50"></i>
                            </div>
                        @endif

                        <span
                            class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-blue-600 shadow-sm transform group-hover:scale-110 transition-transform">
                            {{ ucfirst($article->category ?? 'General') }}
                        </span>
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center justify-between mb-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-history"></i> {{ $article->publishedAt->diffForHumans() }}
                            </span>
                            <span>{{ $article->source->name ?? 'Unknown' }}</span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2 leading-tight">
                            <a href="{{ route('article.show', $article->id) }}"
                                class="hover:text-blue-600 transition-colors">
                                {{ $article->title }}
                            </a>
                        </h2>

                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-grow">
                            {{ Str::limit($article->description, 100) }}
                        </p>

                        <a href="{{ route('article.show', $article->id) }}"
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-semibold mt-auto group/link">
                            Read Article 
                            <i class="fas fa-arrow-right ml-1 group-hover/link:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Empty State with animation --}}
        @if ($articles->isEmpty())
            <div class="text-center py-20 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 animate-fade-in">
                <i class="fas fa-search text-gray-300 text-5xl mb-4 animate-bounce"></i>
                <h3 class="text-xl font-semibold text-gray-600">No articles found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search or category filter.</p>
                <a href="{{ route('home') }}"
                    class="mt-4 inline-block px-6 py-2 bg-white border border-gray-300 rounded-full text-sm hover:bg-gray-50 transition-all hover:scale-105">
                    Clear Filters
                </a>
            </div>
        @endif

        {{-- Pagination with animation --}}
        @if ($articles->hasPages())
            <div class="mt-12 flex justify-center animate-fade-in" style="animation-delay: 0.5s">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- CSS Animations --}}
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out backwards;
        }

        .animate-slide-in-down {
            animation: slideInDown 0.5s ease-out backwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.4s ease-out backwards;
        }

        /* Smooth page transitions */
        @media (prefers-reduced-motion: no-preference) {
            html {
                scroll-behavior: smooth;
            }
        }
    </style>

    {{-- JavaScript to show loading overlay on form submission --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('loading-overlay');
            
            // Listen for form submissions that generate summaries
            document.addEventListener('submit', function(e) {
                const form = e.target;
                const formData = new FormData(form);
                
                // Show overlay only for generate/regenerate actions
                if (formData.get('generate_summary') || formData.get('regenerate_summary')) {
                    if (overlay) {
                        overlay.style.display = 'flex';
                    }
                }
            });

            // Hide overlay when page loads (in case back button was pressed)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted || performance.navigation.type === 2) {
                    if (overlay) {
                        overlay.style.display = 'none';
                    }
                }
            });
        });
    </script>
</x-layout>