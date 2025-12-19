<nav class="bg-transparent absolute top-0 left-0 w-full flex justify-center items-center z-[9999]">
    <div class="container xl:max-w-screen-xl">
        <div class="flex items-center justify-between relative">
            
            {{-- LOGO SECTION (Unchanged) --}}
            <div class="px-4 h-20">
                <a href="/" class="h-full flex items-center">
                    <img src="https://res.cloudinary.com/daavtibr2/image/upload/v1763311265/ucaooxqt7qmkvqgruh4w.png"
                        class="h-10 sm:h-12" alt="Soko Financial Logo" />
                </a>
            </div>

            {{-- MOBILE HAMBURGER (Unchanged) --}}
            <div class="flex items-center px-4 md:hidden">
                <button id="hamburger" name="hamburger" type="button" class="block absolute right-4 group">
                    <span class="hamburger-line transition duration-300 ease-in-out origin-top-left"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out origin-bottom-left"></span>
                </button>
            </div>

            {{-- MAIN MENU (Unchanged) --}}
            <div class="px-4 hidden absolute shadow-lg rounded-b-lg top-full w-full md:rounded-none md:shadow-none md:static md:block md:w-auto"
                id="navbar-menu">
                <ul class="flex-col rounded-b-lg bg-white md:bg-transparent md:flex-row flex md:space-x-2 lg:space-x-12 md:mt-0 text-base md:text-lg md:font-medium">
                    <li>
                        <a href="{{ route('home') }}"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6 md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300 {{ Route::is('home') ? 'underline underline-offset-4 font-medium text-primary-100' : '' }}">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="/about"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6 md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300">
                            About
                        </a>
                    </li>
                    {{-- Your other commented out links are preserved here invisible --}}
                </ul>
            </div>

            {{-- AUTH SECTION (Modified) --}}
            <div class="hidden md:flex items-center gap-4">
                @guest
                    {{-- GUEST STATE: Login & Register Buttons --}}
                    <div class="flex space-x-4">
                        <a href="/login"
                            class="bg-[#41479E] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#333a86] transition duration-300 shadow-sm">
                            Login
                        </a>
                        <a href="/register"
                            class="text-[#41479E] px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition duration-300 border-2 border-[#41479E]">
                            Sign Up
                        </a>
                    </div>
                @endguest

                @auth
                    {{-- LOGGED IN STATE: Avatar Trigger for Sidebar --}}
                    @php
                        $avatar = '';
                        if (auth()->user()->role != 'user' && auth()->user()->avatar == 'Unset') {
                            $avatar = asset('file/blog/user.png');
                        } elseif (auth()->user()->role != 'user' && auth()->user()->avatar != 'Unset') {
                            $avatar = asset('images/admin/profile/' . auth()->user()->avatar);
                        } elseif (auth()->user()->role == 'User' && auth()->user()->avatar == 'Unset') {
                            $avatar = asset('file/blog/user.png');
                        } elseif (auth()->user()->role == 'User' && auth()->user()->avatar != 'Unset') {
                            $avatar = asset('users/image/profile/' . auth()->user()->avatar);
                        }
                    @endphp
                    
                    <button onclick="toggleUserSidebar()" class="relative group focus:outline-none">
                        <div class="flex items-center gap-3 bg-white/50 backdrop-blur-sm pl-4 pr-1 py-1 rounded-full border border-gray-200 hover:border-[#41479E] transition-all duration-300 cursor-pointer">
                            <span class="text-sm font-semibold text-[#41479E]">{{ Str::limit(auth()->user()->name, 10) }}</span>
                            <img class="w-10 h-10 object-cover rounded-full border-2 border-white shadow-sm"
                                src="{{ auth()->user()->avatar == 'Unset' ? asset('file/blog/user.png') : asset('storage/' . auth()->user()->avatar) }}"
                                alt="user avatar">
                        </div>
                    </button>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- USER SIDEBAR COMPONENT (New Addition) --}}
@auth
    <div id="sidebar-backdrop" onclick="toggleUserSidebar()" 
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[10000] hidden transition-opacity duration-300 opacity-0">
    </div>

    <div id="user-sidebar" 
         class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl z-[10001] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
        
        <div class="bg-[#41479E] p-6 text-white relative">
            <button onclick="toggleUserSidebar()" class="absolute top-4 right-4 text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="flex flex-col items-center mt-4">
                <img class="w-20 h-20 object-cover rounded-full border-4 border-white/20 mb-3"
                    src="{{ auth()->user()->avatar == 'Unset' ? asset('file/blog/user.png') : asset('storage/' . auth()->user()->avatar) }}"
                    alt="Profile">
                <h3 class="text-lg font-bold">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-white/70">{{ auth()->user()->email }}</p>
                <span class="mt-2 px-3 py-1 bg-white/20 rounded-full text-xs font-medium backdrop-blur-sm">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-4">
                <a href="{{ auth()->user()->role === 'Admin' ? route('dashboardAdmin') : route('home') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-[#41479E] rounded-lg transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-[#41479E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                {{-- Add more links here like 'Profile Settings', 'My Orders', etc --}}
                <a href="#" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-[#41479E] rounded-lg transition-colors group">
                    <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-[#41479E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    My Profile
                </a>
            </nav>
        </div>

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
@endauth

{{-- JAVASCRIPT FOR SIDEBAR --}}
<script>
    function toggleUserSidebar() {
        const sidebar = document.getElementById('user-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        if (sidebar.classList.contains('translate-x-full')) {
            // Open
            backdrop.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
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
            }, 300); // Match transition duration
        }
    }
</script>