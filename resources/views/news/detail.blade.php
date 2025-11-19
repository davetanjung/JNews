<x-layout>
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Breadcrumb -->
        <nav class="mb-8 flex items-center text-sm text-gray-600">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">
                <i class="fas fa-home mr-1"></i>
                Home
            </a>
            <i class="fas fa-chevron-right mx-3 text-xs"></i>
            <span class="text-gray-800">Article</span>
        </nav>

        <!-- Main Article -->
        <article class="bg-white rounded-xl shadow-lg overflow-hidden mb-12">
            <!-- Article Header Image -->
            <div class="w-full h-96 bg-gradient-to-br from-blue-400 to-purple-500 overflow-hidden">
                @if($article->image)
                    <img src="{{ $article->image }}" 
                         alt="{{ $article->title }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-8xl opacity-50"></i>
                    </div>
                @endif
            </div>

            <!-- Article Content -->
            <div class="p-8 md:p-12">
                <!-- Meta Information -->
                <div class="flex flex-wrap items-center gap-4 mb-6 text-sm text-gray-600">
                    <span class="flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full">
                        <i class="fas fa-tag"></i>
                        {{ $article->source->name ?? 'Unknown Source' }}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="far fa-calendar"></i>
                        {{ $article->publishedAt->format('F j, Y') }}
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="far fa-clock"></i>
                        {{ $article->publishedAt->format('g:i A') }}
                    </span>
                </div>

                <!-- Title -->
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ $article->title }}
                </h1>

                <!-- Description -->
                @if($article->description)
                    <div class="text-xl text-gray-700 mb-8 leading-relaxed font-medium border-l-4 border-blue-500 pl-6 py-2 bg-gray-50">
                        {{ $article->description }}
                    </div>
                @endif

                <!-- Content -->
                @if($article->extended_content)
                    <div class="prose prose-lg max-w-none mb-8">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                            {{ $article->extended_content }}
                        </p>
                    </div>
                @endif

                <!-- Source Link -->
                @if($article->url)
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <a href="{{ $article->url }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <i class="fas fa-external-link-alt"></i>
                            Read Full Article at Source
                        </a>
                    </div>
                @endif

                <!-- Share Section -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Share this article</h3>
                    <div class="flex gap-3">
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('article.show', $article->id)) }}&text={{ urlencode($article->title) }}" 
                           target="_blank"
                           class="flex items-center justify-center w-10 h-10 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('article.show', $article->id)) }}" 
                           target="_blank"
                           class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('article.show', $article->id)) }}&title={{ urlencode($article->title) }}" 
                           target="_blank"
                           class="flex items-center justify-center w-10 h-10 bg-blue-700 text-white rounded-full hover:bg-blue-800 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <button onclick="copyToClipboard()" 
                                class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition-colors">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related Articles -->
        @if($relatedArticles->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-6">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedArticles as $related)
                        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <!-- Related Article Image -->
                            <div class="h-40 bg-gradient-to-br from-blue-400 to-purple-500 overflow-hidden">
                                @if($related->image)
                                    <img src="{{ $related->image }}" 
                                         alt="{{ $related->title }}" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-newspaper text-white text-4xl opacity-50"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Related Article Content -->
                            <div class="p-5">
                                <div class="text-xs text-gray-500 mb-2">
                                    {{ $related->publishedAt->format('M d, Y') }}
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2 hover:text-blue-600 transition-colors">
                                    <a href="{{ route('article.show', $related->id) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>
                                <a href="{{ route('article.show', $related->id) }}" 
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                    Read More 
                                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Back to Home Button -->
        <div class="text-center">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                <i class="fas fa-arrow-left"></i>
                Back to All Articles
            </a>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                // Show success message
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'success',
                    title: 'Link copied to clipboard!'
                });
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</x-layout>