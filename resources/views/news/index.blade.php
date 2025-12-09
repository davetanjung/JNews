<x-layout>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Latest Articles</h1>
            <p class="text-gray-600 text-lg">Discover our curated collection of stories and insights</p>
        </div>

        <div class="mb-6 max-w-2xl mx-auto">
            <form action="{{ route('home') }}" method="GET" class="relative">

                {{-- PRO TIP: Hidden Input to keep the category selected when searching --}}
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div class="flex gap-2">
                    <div class="relative flex-grow">
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                            placeholder="Search articles..."
                            class="w-full px-4 py-3 pl-12 pr-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Search
                    </button>

                    {{-- Clear Button --}}
                    @if ($search || request('category'))
                        <a href="{{ route('home') }}"
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium flex items-center">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- category picker --}}
        <div class="mb-8 flex justify-center">
            <x-category-bar :selected="request('category')" :summary="$summary" />
        </div>

        @if ($search || request('category'))
            <div class="mb-6 text-center">
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($articles as $article)
                <article
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">
                    <div class="h-48 bg-gray-200 overflow-hidden relative">
                        @if ($article->image)
                            <img src="{{ $article->image }}" alt="{{ $article->title }}"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <div
                                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-purple-500">
                                <i class="fas fa-newspaper text-white text-4xl opacity-50"></i>
                            </div>
                        @endif

                        <span
                            class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-blue-600 shadow-sm">
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
                            class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-semibold mt-auto">
                            Read Article <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($articles->isEmpty())
            <div class="text-center py-20 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600">No articles found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search or category filter.</p>
                <a href="{{ route('home') }}"
                    class="mt-4 inline-block px-6 py-2 bg-white border border-gray-300 rounded-full text-sm hover:bg-gray-50">
                    Clear Filters
                </a>
            </div>
        @endif

        @if ($articles->hasPages())
            <div class="mt-12 flex justify-center">
                {{-- This appends the current search/category to the pagination links automatically --}}
                {{ $articles->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-layout>
