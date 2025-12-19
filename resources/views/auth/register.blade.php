<x-layout>
    {{-- FIX: Added 'w-full' to make the background stretch across the whole screen --}}
    <div class="w-full min-h-screen flex items-center justify-center px-0 sm:px-6 lg:px-8 bg-[#f5f4f1]">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
            
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Create Account
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Join us to start your journey
                </p>
            </div>

            {{-- FIX: Added Error Display Block (Essential for Registration) --}}
            @if ($errors->any())
                <div class="bg-red-50 text-red-500 text-sm p-4 rounded-lg border border-red-100">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
                @csrf
                
                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input id="name" name="name" type="text" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="John Doe">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input id="email" name="email" type="email" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="name@example.com">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="Create a password">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                            class="appearance-none block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#41479E] focus:border-transparent transition duration-300" 
                            placeholder="Repeat your password">
                    </div>
                </div>

                {{-- Submit --}}
                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-[#41479E] hover:bg-[#333a86] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#41479E] transition duration-300 shadow-md hover:shadow-lg">
                        Sign Up
                    </button>
                </div>
            </form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="font-bold text-[#41479E] hover:text-[#333a86] transition duration-300">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-layout>