<x-layout>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Latest Articles</h1>
            <p class="text-gray-600 text-lg">Discover our curated collection of stories and insights</p>
        </div>

        <!-- Search Bar -->
        <div class="mb-8 max-w-2xl mx-auto">
            <form action="{{ route('home') }}" method="GET" class="relative">
                <div class="flex gap-2">
                    <div class="relative flex-grow">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search ?? '' }}"
                            placeholder="Search articles by title, description, or source..." 
                            class="w-full px-4 py-3 pl-12 pr-4 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Search
                    </button>
                    @if($search)
                        <a 
                            href="{{ route('home') }}" 
                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                        >
                            Clear
                        </a>
                    @endif
                </div>
            </form>
            
            @if($search)
                <p class="mt-3 text-gray-600 text-sm">
                    Showing results for: <span class="font-semibold">"{{ $search }}"</span>
                    ({{ $articles->total() }} {{ $articles->total() === 1 ? 'result' : 'results' }})
                </p>
            @endif
        </div>

        <!-- Articles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($articles as $article)
                <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">
                    <!-- Article Image -->
                    <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 overflow-hidden">
                        @if($article->image)
                            <img src="{{ $article->image }}" 
                                 alt="{{ $article->title }}" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-newspaper text-white text-5xl opacity-50"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Article Content -->
                    <div class="p-6 flex flex-col flex-grow">
                        <!-- Source & Date -->
                        <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-tag"></i>
                                {{ $article->source->name ?? 'Unknown Source' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <i class="far fa-calendar"></i>
                                {{ $article->publishedAt->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h2 class="text-xl font-semibold text-gray-800 mb-3 line-clamp-2 hover:text-blue-600 transition-colors">
                            <a href="{{ route('article.show', $article->id) }}">
                                {{ $article->title }}
                            </a>
                        </h2>

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-grow">
                            {{ $article->description }}
                        </p>

                        <!-- Read More Button -->
                        <a href="{{ route('article.show', $article->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors mt-auto">
                            Read More 
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Empty State -->
        @if($articles->isEmpty())
            <div class="text-center py-16">
                <i class="fas fa-newspaper text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">
                    @if($search)
                        No Articles Found for "{{ $search }}"
                    @else
                        No Articles Found
                    @endif
                </h3>
                <p class="text-gray-500">
                    @if($search)
                        Try searching with different keywords
                    @else
                        Check back later for new content
                    @endif
                </p>
            </div>
        @endif

        <!-- Pagination -->
        @if($articles->hasPages())
            <div class="mt-12">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</x-layout>