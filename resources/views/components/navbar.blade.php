<nav class="bg-transparent absolute top-0 left-0 w-full flex justify-center items-center z-[9999]">
    <div class="container xl:max-w-screen-xl">
        <div class="flex items-center justify-between relative">
            
            {{-- LOGO --}}
            <div class="px-4 h-20">
                <a href="/" class="h-full flex items-center">
                    <img src="https://res.cloudinary.com/daavtibr2/image/upload/v1763311265/ucaooxqt7qmkvqgruh4w.png"
                        class="h-10 sm:h-12" alt="Soko Financial Logo" />
                </a>
            </div>

            {{-- MOBILE HAMBURGER --}}
            <div class="flex items-center px-4 md:hidden">
                <button id="hamburger" name="hamburger" type="button" class="block absolute right-4 group">
                    <span class="hamburger-line transition duration-300 ease-in-out origin-top-left"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out origin-bottom-left"></span>
                </button>
            </div>

            {{-- MENU --}}
            <div class="px-4 hidden absolute shadow-lg rounded-b-lg top-full w-full md:rounded-none md:shadow-none md:static md:block md:w-auto"
                id="navbar-menu">
                <ul class="flex-col rounded-b-lg bg-white md:bg-transparent md:flex-row flex md:space-x-2 lg:space-x-12 md:mt-0 text-base md:text-lg md:font-medium">
                </ul>
            </div>

            {{-- AUTH SECTION --}}
            <div class="hidden md:flex items-center gap-4">
                @guest
                    <div class="flex space-x-4">
                        <a href="/login" class="bg-[#41479E] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#333a86] transition duration-300 shadow-sm">
                            Login
                        </a>
                        <a href="/register" class="text-[#41479E] px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition duration-300 border-2 border-[#41479E]">
                            Sign Up
                        </a>
                    </div>
                @endguest

                @auth
                    {{-- TRIGGER BUTTON (This calls the JS inside the component) --}}
                    @php
                        $avatar = auth()->user()->avatar == 'Unset' 
                            ? asset('file/blog/user.png') 
                            : (auth()->user()->role == 'User' ? asset('users/image/profile/' . auth()->user()->avatar) : asset('images/admin/profile/' . auth()->user()->avatar));
                    @endphp
                    
                    <button onclick="toggleUserSidebar()" class="relative group focus:outline-none">
                        <div class="flex items-center gap-3 bg-white/50 backdrop-blur-sm pl-4 pr-1 py-1 rounded-full border border-gray-200 hover:border-[#41479E] transition-all duration-300 cursor-pointer">
                            <span class="text-sm font-semibold text-[#41479E]">{{ Str::limit(auth()->user()->name, 10) }}</span>
                            {{-- <img class="w-10 h-10 object-cover rounded-full border-2 border-white shadow-sm"
                                src="{{ $avatar }}"
                                alt="user avatar"> --}}
                        </div>
                    </button>
                    
                    {{-- CALL THE SIDEBAR COMPONENT --}}
                    <x-user-sidebar />
                @endauth
            </div>
        </div>
    </div>
</nav>