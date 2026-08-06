<div class="flex min-h-[70vh] flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-violet-600 to-indigo-500 shadow-xl shadow-violet-600/30 mb-4">
                <span class="text-2xl font-bold text-white">F</span>
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight text-white">Sign in to your account</h2>
            <p class="mt-2 text-sm text-slate-400">Enter your credentials to manage AI configuration.</p>
        </div>

        <!-- Alert messages -->
        @if ($errorMessage)
            <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-400 flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif

        <!-- Card container -->
        <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-8 backdrop-blur-md shadow-2xl">
            <form wire:submit.prevent="login" class="space-y-6">
                
                <!-- Email Address -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-slate-300">Email Address</label>
                    <input 
                        id="email" 
                        type="email" 
                        wire:model.defer="email"
                        placeholder="admin@example.com"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    @error('email') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-slate-300">Password</label>
                    <input 
                        id="password" 
                        type="password" 
                        wire:model.defer="password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    @error('password') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Remember Me & Reset -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            type="checkbox" 
                            wire:model.defer="remember"
                            class="h-4 w-4 rounded border-slate-800 bg-slate-950 text-violet-600 focus:ring-violet-500 focus:ring-offset-slate-950">
                        <label for="remember" class="ml-2 block text-xs text-slate-400">Remember me</label>
                    </div>
                </div>

                <!-- Sign in button -->
                <div>
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="group relative flex w-full justify-center rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 transition-all disabled:opacity-55">
                        <span wire:loading.remove wire:target="login">Sign in</span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Signing in...
                        </span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Seed notice helper -->
        <p class="text-center text-xs text-slate-500">
            Seed accounts: <code class="text-violet-400">admin@example.com</code> / <code class="text-violet-400">password</code>
        </p>
    </div>
</div>
