<div>
    {{-- BACKDROP --}}
    <div id="sidebar-backdrop" onclick="toggleUserSidebar()" 
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[10000] hidden transition-opacity duration-300 opacity-0">
    </div>

    {{-- SIDEBAR PANEL --}}
    <div id="user-sidebar" 
         class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl z-[10001] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        
        {{-- HEADER --}}
        <div class="bg-[#41479E] p-6 text-white relative">
            <button onclick="toggleUserSidebar()" class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="flex flex-col items-center mt-4">
           <img class="w-20 h-20 object-cover rounded-full border-4 border-white/20 mb-3"
     src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}"
     alt="Profile">
                <h3 class="text-lg font-bold">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-white/70">{{ auth()->user()->email }}</p>
                <span class="mt-2 px-3 py-1 bg-white/20 rounded-full text-xs font-medium backdrop-blur-sm">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>
        </div>

        {{-- LINKS --}}
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-4">
                <a href="{{ auth()->user()->role === 'Admin' ? route('dashboardAdmin') : route('home') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-[#41479E] rounded-lg transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-[#41479E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                <a href="/my-summaries" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-[#41479E] rounded-lg transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-[#41479E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    My Summaries
                </a>
            </nav>
        </div>

        {{-- LOGOUT --}}
        <div class="p-4 border-t border-gray-100">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT --}}
<script>
    function toggleUserSidebar() {
        const sidebar = document.getElementById('user-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        if (sidebar.classList.contains('translate-x-full')) {
            // Open
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                sidebar.classList.remove('translate-x-full');
            }, 10);
        } else {
            // Close
            sidebar.classList.add('translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
    }
</script>