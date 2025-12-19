<x-layout>
    {{-- FIX: Added 'w-full' to make the background stretch across the whole screen --}}
    <div class="w-full min-h-screen flex items-center justify-center px-0 sm:px-6 lg:px-8 bg-[#f5f4f1]">
        
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
            
            {{-- Header --}}
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Welcome Back
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Please sign in to your account
                </p>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-50 text-red-500 text-sm p-4 rounded-lg border border-red-100">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                
                <div class="rounded-md space-y-4">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="Enter your email">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="Enter your password">
                    </div>
                </div>

                {{-- FIX: Improved spacing for Remember Me / Forgot Password --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                            class="h-4 w-4 text-[#41479E] focus:ring-[#41479E] border-gray-300 rounded cursor-pointer">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900 cursor-pointer select-none">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-[#41479E] hover:text-[#333a86] transition duration-300">
                            Forgot password?
                        </a>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-[#41479E] hover:bg-[#333a86] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#41479E] transition duration-300 shadow-md hover:shadow-lg">
                        Sign In
                    </button>
                </div>
            </form>

            {{-- Register Link --}}
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-bold text-[#41479E] hover:text-[#333a86] transition duration-300">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-layout>