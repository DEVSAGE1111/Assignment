<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-900 antialiased selection:bg-violet-500 selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Formify AI - AI Powered Form Builder' }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $aiSetting = \App\Models\AiSetting::first();
    $aiEnabled = false;
    if ($aiSetting && $aiSetting->enabled) {
        $provider = $aiSetting->active_provider;
        $settings = $aiSetting->provider_settings[$provider] ?? [];
        if ($provider === 'ollama') {
            $aiEnabled = !empty($settings['url']);
        } else {
            $aiEnabled = !empty($settings['api_key']);
        }
    }
@endphp
<body class="flex min-h-full flex-col relative bg-slate-50 text-slate-900">
    <!-- Subtle dotted grid background (Professional human-designed touch) -->
    <div class="pointer-events-none fixed inset-0 z-0 opacity-40 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:20px_20px]"></div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-8">
                <!-- Typography logo instead of AI generic gradients -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <div class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-900 font-mono font-bold text-sm transition-colors group-hover:border-violet-500">
                        F
                    </div>
                    <span class="text-md font-semibold tracking-tight text-slate-900 transition-colors group-hover:text-slate-800">
                        formify<span class="text-slate-500 font-normal text-[10px] border border-slate-200 rounded px-1.5 py-0.5 ml-1.5 bg-slate-100">AI</span>
                    </span>
                </a>

                @auth
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('home') }}" class="text-xs font-semibold tracking-wide transition-colors hover:text-slate-900 {{ request()->routeIs('home') ? 'text-violet-600' : 'text-slate-500' }}">
                            DASHBOARD
                        </a>
                        <a href="{{ route('forms.create') }}" class="text-xs font-semibold tracking-wide transition-colors hover:text-slate-900 {{ request()->routeIs('forms.create') ? 'text-violet-600' : 'text-slate-500' }}">
                            CREATE FORM
                        </a>
                        <a href="{{ route('api-docs') }}" class="text-xs font-semibold tracking-wide transition-colors hover:text-slate-900 {{ request()->routeIs('api-docs') ? 'text-violet-600' : 'text-slate-500' }}">
                            API DOCS
                        </a>
                        <a href="{{ route('ai-settings') }}" class="text-xs font-semibold tracking-wide transition-colors hover:text-slate-900 {{ request()->routeIs('ai-settings') ? 'text-violet-600' : 'text-slate-500' }}">
                            AI SETTINGS
                        </a>
                    </nav>
                @endauth
            </div>

            <div class="flex items-center gap-4">
                @auth
                    @if(!$aiEnabled)
                        <!-- Clean outlining status badges -->
                        <div class="hidden lg:flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-500">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Mock Engine Active
                        </div>
                    @else
                        <div class="hidden lg:flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            AI Node Online
                        </div>
                    @endif

                    <span class="hidden sm:inline text-xs text-slate-500 font-medium">Hi, {{ Auth::user()->name }}</span>
                    
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors bg-transparent border-0 cursor-pointer py-1.5 px-3 rounded-lg hover:bg-slate-100">
                            Logout
                        </button>
                    </form>

                    <!-- Flat white premium button layout -->
                    <a href="{{ route('forms.create') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-3.5 py-1.5 text-xs font-semibold text-white transition-all hover:bg-slate-800">
                        New Form
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:border-slate-350 hover:text-slate-900">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Mobile Nav Bar (bottom stick for nice mobile UX) -->
    @auth
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 flex h-14 border-t border-slate-200 bg-white/95 backdrop-blur-xl px-4 justify-around items-center">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('home') ? 'text-violet-600' : 'text-slate-400' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span class="text-[9px] font-semibold tracking-wide">DASHBOARD</span>
            </a>
            <a href="{{ route('forms.create') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('forms.create') ? 'text-violet-600' : 'text-slate-400' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-[9px] font-semibold tracking-wide">NEW FORM</span>
            </a>
            <a href="{{ route('api-docs') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('api-docs') ? 'text-violet-600' : 'text-slate-400' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-[9px] font-semibold tracking-wide">API DOCS</span>
            </a>
            <a href="{{ route('ai-settings') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('ai-settings') ? 'text-violet-600' : 'text-slate-400' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-[9px] font-semibold tracking-wide">AI SETTINGS</span>
            </a>
        </nav>
    @endauth

    <!-- Main Content -->
    <main class="flex-grow z-10 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full pb-24 md:pb-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500 z-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} Formify AI. Crafted by human engineers, powered by Laravel 13, Livewire 4, and Tailwind CSS v4.</p>
        </div>
    </footer>
</body>
</html>
