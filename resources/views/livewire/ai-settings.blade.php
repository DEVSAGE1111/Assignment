<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h3 class="text-3xl font-bold tracking-tight text-white">AI Settings</h3>
        <p class="mt-2 text-sm text-slate-400">Configure active AI integrations, API keys, URLs, and models.</p>
    </div>

    <!-- Feedback Alerts -->
    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-400 flex items-center gap-3">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl">
        <!-- Settings Form -->
        <form wire:submit.prevent="saveSettings" class="space-y-6">
            
            <!-- Global Config Card -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 sm:p-8 backdrop-blur-md shadow-2xl space-y-6">
                
                <!-- Notice Box -->
                <div class="rounded-xl border border-violet-500/20 bg-violet-500/5 p-4 text-xs text-violet-400 flex items-start gap-3">
                    <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-6h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-bold block mb-0.5">Secure Credentials</span>
                        Your API keys are encrypted and stored safely in the database.
                    </div>
                </div>

                <!-- Master Toggle -->
                <div class="flex items-start">
                    <div class="flex h-6 items-center">
                        <input 
                            id="enabled" 
                            type="checkbox" 
                            wire:model.live="enabled" 
                            class="h-4 w-4 rounded border-slate-800 bg-slate-950 text-violet-600 focus:ring-violet-500 focus:ring-offset-slate-950">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="enabled" class="font-semibold text-white">Enable AI Integration</label>
                        <p class="text-xs text-slate-500 mt-1">Master switch to turn AI features on or off globally.</p>
                    </div>
                </div>

                <!-- Active Provider Selector -->
                <div class="space-y-2">
                    <label for="activeProvider" class="block text-sm font-semibold text-slate-300">Select Active API Provider <span class="text-red-500">*</span></label>
                    <select 
                        id="activeProvider"
                        wire:model.live="activeProvider" 
                        class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="gemini">Google Gemini</option>
                        <option value="openai">ChatGPT (OpenAI)</option>
                        <option value="xai">X (xAI / Grok)</option>
                        <option value="ollama">Ollama (Local AI)</option>
                    </select>
                    <p class="text-[11px] text-slate-500">Select which provider to use. Only its configuration fields will be displayed below.</p>
                </div>
            </div>

            <!-- Dynamic Provider Credentials Card -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 sm:p-8 backdrop-blur-md shadow-2xl">
                
                <!-- 1. GOOGLE GEMINI FIELDS -->
                @if($activeProvider === 'gemini')
                    <div class="space-y-6">
                        <h4 class="text-md font-bold text-white border-b border-slate-900 pb-3 mb-4">Google Gemini Configuration</h4>
                        
                        <!-- API Key -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">API Key <span class="text-red-500">*</span></label>
                            <div class="relative rounded-xl shadow-sm">
                                <input 
                                    type="{{ $showKeys['gemini'] ? 'text' : 'password' }}" 
                                    wire:model="providerSettings.gemini.api_key"
                                    placeholder="Enter your Gemini API key"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 pl-4 pr-12 py-3 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                <button 
                                    type="button" 
                                    wire:click="toggleKeyVisibility('gemini')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                                    @if($showKeys['gemini'])
                                        <!-- Eye slash icon -->
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @else
                                        <!-- Eye icon -->
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @endif
                                </button>
                            </div>
                            @error('providerSettings.gemini.api_key') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Model Dropdown -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Select Model</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="providerSettings.gemini.model"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    @foreach($availableModels as $m)
                                        <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <button 
                                    type="button" 
                                    wire:click="fetchModelsFromApi"
                                    wire:loading.attr="disabled"
                                    title="Fetch models dynamically from API"
                                    class="flex h-11 w-11 items-center justify-center shrink-0 rounded-xl border border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-55">
                                    <svg wire:loading.remove wire:target="fetchModelsFromApi" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.5" />
                                    </svg>
                                    <svg wire:loading wire:target="fetchModelsFromApi" class="animate-spin h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Model Textbox -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Or enter Custom Model (Optional)</label>
                            <input 
                                type="text"
                                wire:model="providerSettings.gemini.custom_model"
                                placeholder="e.g. gemini-2.0-pro-exp"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>
                    </div>
                @endif

                <!-- 2. OPENAI FIELDS -->
                @if($activeProvider === 'openai')
                    <div class="space-y-6">
                        <h4 class="text-md font-bold text-white border-b border-slate-900 pb-3 mb-4">ChatGPT (OpenAI) Configuration</h4>
                        
                        <!-- API Key -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">API Key <span class="text-red-500">*</span></label>
                            <div class="relative rounded-xl shadow-sm">
                                <input 
                                    type="{{ $showKeys['openai'] ? 'text' : 'password' }}" 
                                    wire:model="providerSettings.openai.api_key"
                                    placeholder="Enter your OpenAI API key"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 pl-4 pr-12 py-3 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                <button 
                                    type="button" 
                                    wire:click="toggleKeyVisibility('openai')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                                    @if($showKeys['openai'])
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @endif
                                </button>
                            </div>
                            @error('providerSettings.openai.api_key') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Model Dropdown -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Select Model</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="providerSettings.openai.model"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    @foreach($availableModels as $m)
                                        <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <button 
                                    type="button" 
                                    wire:click="fetchModelsFromApi"
                                    wire:loading.attr="disabled"
                                    title="Fetch models dynamically from API"
                                    class="flex h-11 w-11 items-center justify-center shrink-0 rounded-xl border border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-55">
                                    <svg wire:loading.remove wire:target="fetchModelsFromApi" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.5" />
                                    </svg>
                                    <svg wire:loading wire:target="fetchModelsFromApi" class="animate-spin h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Model Textbox -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Or enter Custom Model (Optional)</label>
                            <input 
                                type="text"
                                wire:model="providerSettings.openai.custom_model"
                                placeholder="e.g. o1-preview"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>
                    </div>
                @endif

                <!-- 3. X (xAI GROK) FIELDS -->
                @if($activeProvider === 'xai')
                    <div class="space-y-6">
                        <h4 class="text-md font-bold text-white border-b border-slate-900 pb-3 mb-4">X (xAI / Grok) Configuration</h4>
                        
                        <!-- API Key -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">API Key <span class="text-red-500">*</span></label>
                            <div class="relative rounded-xl shadow-sm">
                                <input 
                                    type="{{ $showKeys['xai'] ? 'text' : 'password' }}" 
                                    wire:model="providerSettings.xai.api_key"
                                    placeholder="Enter your Grok API key"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 pl-4 pr-12 py-3 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                <button 
                                    type="button" 
                                    wire:click="toggleKeyVisibility('xai')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                                    @if($showKeys['xai'])
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @endif
                                </button>
                            </div>
                            @error('providerSettings.xai.api_key') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Model Dropdown -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Select Model</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="providerSettings.xai.model"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    @foreach($availableModels as $m)
                                        <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <button 
                                    type="button" 
                                    wire:click="fetchModelsFromApi"
                                    wire:loading.attr="disabled"
                                    title="Fetch models dynamically from API"
                                    class="flex h-11 w-11 items-center justify-center shrink-0 rounded-xl border border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-55">
                                    <svg wire:loading.remove wire:target="fetchModelsFromApi" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.5" />
                                    </svg>
                                    <svg wire:loading wire:target="fetchModelsFromApi" class="animate-spin h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Model Textbox -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Or enter Custom Model (Optional)</label>
                            <input 
                                type="text"
                                wire:model="providerSettings.xai.custom_model"
                                placeholder="e.g. grok-vision"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>
                    </div>
                @endif

                <!-- 4. OLLAMA FIELDS -->
                @if($activeProvider === 'ollama')
                    <div class="space-y-6">
                        <h4 class="text-md font-bold text-white border-b border-slate-900 pb-3 mb-4">Ollama (Local AI) Configuration</h4>
                        
                        <!-- Ollama URL -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Ollama URL (Base endpoint) <span class="text-red-500">*</span></label>
                            <input 
                                type="text"
                                wire:model="providerSettings.ollama.url"
                                placeholder="http://localhost:11434"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                            <p class="text-[10px] text-slate-500">Local or remote Ollama endpoint. E.g. http://localhost:11434</p>
                            @error('providerSettings.ollama.url') <span class="text-xs text-red-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Optional API key -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">API Key (Optional)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <input 
                                    type="{{ $showKeys['ollama'] ? 'text' : 'password' }}" 
                                    wire:model="providerSettings.ollama.api_key"
                                    placeholder="Enter authorization key if proxying"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 pl-4 pr-12 py-3 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                <button 
                                    type="button" 
                                    wire:click="toggleKeyVisibility('ollama')"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300">
                                    @if($showKeys['ollama'])
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    @endif
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-500">Optional API key/Token for authenticated Ollama reverse proxies.</p>
                        </div>

                        <!-- Model Dropdown -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Select Model</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="providerSettings.ollama.model"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    @foreach($availableModels as $m)
                                        <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <button 
                                    type="button" 
                                    wire:click="fetchModelsFromApi"
                                    wire:loading.attr="disabled"
                                    title="Fetch models dynamically from API"
                                    class="flex h-11 w-11 items-center justify-center shrink-0 rounded-xl border border-slate-800 bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-55">
                                    <svg wire:loading.remove wire:target="fetchModelsFromApi" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.5" />
                                    </svg>
                                    <svg wire:loading wire:target="fetchModelsFromApi" class="animate-spin h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Model Textbox -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Or enter Custom Model (Optional)</label>
                            <input 
                                type="text"
                                wire:model="providerSettings.ollama.custom_model"
                                placeholder="e.g. codegemma"
                                class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white placeholder-slate-650 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>
                    </div>
                @endif
            </div>

            <!-- Connection Status Banner -->
            @if($connectionStatus)
                <div class="rounded-xl p-4 text-xs font-semibold backdrop-blur-sm border flex items-center gap-3 transition-all duration-300 {{ $connectionSuccess ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-red-500/20 bg-red-500/10 text-red-400' }}">
                    @if($connectionSuccess)
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    @else
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    @endif
                    <span>{{ $connectionStatus }}</span>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-900">
                <button 
                    type="button" 
                    wire:click="checkConnection"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-55">
                    <span wire:loading.remove wire:target="checkConnection">Check Connection</span>
                    <span wire:loading wire:target="checkConnection" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Testing...
                    </span>
                </button>

                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 transition-all">
                    Save AI Settings
                </button>
            </div>
        </form>
    </div>
</div>
