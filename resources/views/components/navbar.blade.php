<nav class="bg-transparent absolute top-0 left-0 w-full flex justify-center items-center z-[9999]">
    <div class="container xl:max-w-screen-xl">
        <div class="flex items-center justify-between relative">
            <div class="px-4 h-20">
                <a href="/" class="h-full flex items-center">
                    <img src="https://res.cloudinary.com/daavtibr2/image/upload/v1763311265/ucaooxqt7qmkvqgruh4w.png" class="h-10 sm:h-12" alt="Soko Financial Logo" />
                </a>
            </div>

            <div class="flex items-center px-4 md:hidden">
                <button id="hamburger" name="hamburger" type="button" class="block absolute right-4 group">
                    <span class="hamburger-line transition duration-300 ease-in-out origin-top-left"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out"></span>
                    <span class="hamburger-line transition duration-300 ease-in-out origin-bottom-left"></span>
                </button>
            </div>

            <div class="px-4 hidden absolute shadow-lg rounded-b-lg top-full w-full md:rounded-none md:shadow-none md:static md:block md:w-auto"
                id="navbar-menu">
                <ul
                    class="flex-col rounded-b-lg bg-white md:bg-transparent md:flex-row flex md:space-x-2 lg:space-x-12 md:mt-0 text-base md:text-lg md:font-medium">
                    <li>
                        <a href="/about"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6
                            md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300">
                            Tentang
                        </a>
                    </li>
                   <li class="relative group">
                        <a href="/service"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6
                            md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300 
                            flex items-center justify-between md:justify-center">
                            Layanan
                            <svg class="w-4 h-4 ml-1 transition-transform duration-300 group-hover:rotate-180" 
                                 fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </a>
                        <div class="absolute left-0 mt-2 w-64 bg-white rounded-lg shadow-lg opacity-0 invisible 
                                    group-hover:opacity-100 group-hover:visible transition-all duration-300 
                                    transform group-hover:translate-y-0 translate-y-2 z-50
                                    md:left-1/2 md:-translate-x-1/2">
                            <ul class="py-2">
                                <li>
                                    <a href="/layanan-keuangan"
                                        class="block px-6 py-3 text-gray-700 hover:bg-[#f5f4f1] hover:text-primary-100 
                                               transition duration-300 font-medium">
                                        Layanan Keuangan
                                    </a>
                                </li>
                                <li>
                                    <a href="/financial-consulting"
                                        class="block px-6 py-3 text-gray-700 hover:bg-[#f5f4f1] hover:text-primary-100 
                                               transition duration-300 font-medium">
                                        Layanan Konsultasi
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="/contact"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6
                            md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300">
                            Kontak
                        </a>
                    </li>
                    <li>
                        <a href="/budget"
                            class="text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6
                            md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300">
                            Kalkulator Finance
                        </a>
                    </li>
                    <li>
                        <a href="/blog"
                            class="text-main-text hover:bg-[#f5f4f1] border-[#f5f4f1] px-6
                            md:hover:bg-transparent md:border-0 block py-2 hover:text-primary-100 md:p-0 duration-300 hover:rounded-b-lg">
                            Blog
                        </a>
                    </li>
                    @if (auth()->user() == null)
                        <div class="flex space-x-6 ml-auto">
                            <a href="/login"
                                class="bg-[#41479E] text-white px-4 py-1.5 rounded-md text-sm block text-center
                                    hover:bg-[#333a86] transition duration-300
                                    md:bg-[#41479E] md:text-white md:hover:bg-[#333a86] md:border-0
                                    md:px-3 md:py-1 button">
                                Login
                            </a>
                            <a href="/user-register"
                            class="text-[#41479E] px-4 py-1.5 rounded-md text-sm block text-center
                                    transition duration-300
                                    md:border-0
                                    md:px-3 md:py-1 button"
                            style="color: #41479E !important; border: 2px solid #41479E !important;">
                            Daftar
                            </a>

                        </div>
                    @endif
                   {{-- <li>
              <button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar"
                class="font-bold text-main-text hover:bg-[#f5f4f1] border-b border-[#f5f4f1] px-6 md:hover:bg-transparent md:border-0 py-2 md:hover:text-primary-100 md:p-0 duration-300 flex items-center justify-between w-full md:w-auto">Produk
                Kami
                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"></path>
                </svg>
              </button>
              <div id="dropdownNavbar"
                class="hidden bg-white text-base z-10 list-none divide-y divide-gray-100 rounded shadow my-4 w-72">
                <ul class="py-1" aria-labelledby="dropdownLargeButton">
                  <li>
                    <a href="/kelas-keuangan-siswa-mahasiswa"
                      class="font-bold hover:bg-[#f5f4f1] text-gray-700 block px-4 py-2">Kelas
                      Keuangan Siswa/Mahasiswa</a>
                  </li>
                  <li>
                    <a href="/kelas-keuangan-umum"
                      class="font-bold hover:bg-[#f5f4f1] text-gray-700 block px-4 py-2">Kelas
                      Keungan Umum</a>
                  </li>
                  <li>
                    <a href="/konsultasi-keuangan"
                      class="font-bold hover:bg-[#f5f4f1] text-gray-700 block px-4 py-2">Konsultasi
                      Keuangan</a>
                  </li>
                  <li>
                    <a href="/webinar" class="font-bold hover:bg-[#f5f4f1] text-gray-700 block px-4 py-2">Webinar</a>
                  </li>
                </ul>
              </div>
            </li> --}}
                </ul>
            </div>

            @if (auth()->user() !== null)
                <div
                    class="--user-profile items-center gap-4 hidden absolute shadow-lg rounded-b-lg top-full w-full md:rounded-none md:shadow-none md:static md:flex md:w-auto">
                    @if (auth()->check())
                    <a href="{{
                        auth()->user()->role === 'Admin'
                            ? route('dashboardAdmin')
                            : route('dashboard')
                    }}"
                            class="p-2 bg-[#f5f4f1] text-[#171717] rounded-[10px] font-bold">Dashboard</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="button" id="logout-button"
                                class="p-2 bg-[#f5f4f1] text-[#171717] rounded-[10px] font-bold text-red-600 ml-2">
                                Logout
                            </button>
                        </form>
                    @else
                        {{-- <form id="logout-form" action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="button" id="logout-button"
                                class="p-2 bg-[#f5f4f1] text-[#171717] rounded-[10px] font-bold text-red-600">
                                Logout
                            </button>
                        </form> --}}
                    @endif




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
                    <img class="w-10 h-10 object-cover rounded-full"
                        src="{{ auth()->user()->avatar == 'Unset' ? asset('file/blog/user.png') : asset('storage/' . auth()->user()->avatar) }}"
                        alt="user avatar">
                </div>
            @endif
        </div>
    </div>
</nav>
