<x-layout>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header Section -->
        <div class="mb-12 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Latest Articles</h1>
            <p class="text-gray-600 text-lg">Discover our curated collection of stories and insights</p>
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
                            {{ $article->title }}
                        </h2>

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3 flex-grow">
                            {{ $article->description }}
                        </p>

                        <!-- Read More Button -->
                        <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors mt-auto">
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
                <h3 class="text-2xl font-semibold text-gray-600 mb-2">No Articles Found</h3>
                <p class="text-gray-500">Check back later for new content</p>
            </div>
        @endif
    </div>
</x-layout>