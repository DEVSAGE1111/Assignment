<div>
    <div class="mx-auto max-w-2xl">
        <!-- Success State -->
        @if ($submitted)
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-8 text-center backdrop-blur-md shadow-2xl relative overflow-hidden">
                <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/10 blur-xl"></div>
                
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400 mb-6">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-white mb-2">Submission Successful!</h2>
                <p class="text-sm text-slate-400 mb-8 leading-relaxed">
                    {{ session('message') ?? 'Thank you for your feedback! Your response has been recorded.' }}
                </p>

                <div class="flex justify-center gap-4">
                    <button 
                        type="button" 
                        wire:click="$set('submitted', false)"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 border border-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-850 hover:text-white transition-colors">
                        Submit Another Response
                    </button>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 transition-all">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @else
            <!-- Form Rendering State -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 sm:p-8 backdrop-blur-md shadow-2xl">
                <!-- Form Header -->
                <div class="mb-8 border-b border-slate-900 pb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ $form->title }}</h2>
                    @if($form->description)
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $form->description }}</p>
                    @endif
                </div>

                <!-- Form Body -->
                <form wire:submit.prevent="submit" class="space-y-6">
                    @foreach($form->fields as $field)
                        <div class="space-y-2">
                            <!-- Label & Required star -->
                            <label for="input-{{ $field['id'] }}" class="block text-sm font-medium text-slate-300">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false)
                                    <span class="text-red-500" title="Required">*</span>
                                @endif
                            </label>

                            <!-- TEXT INPUT -->
                            @if($field['type'] === 'text')
                                <input 
                                    type="text" 
                                    id="input-{{ $field['id'] }}"
                                    wire:model="data.{{ $field['id'] }}"
                                    placeholder="{{ $field['placeholder'] }}"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">

                            <!-- EMAIL INPUT -->
                            @elseif($field['type'] === 'email')
                                <input 
                                    type="email" 
                                    id="input-{{ $field['id'] }}"
                                    wire:model="data.{{ $field['id'] }}"
                                    placeholder="{{ $field['placeholder'] }}"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">

                            <!-- NUMBER INPUT -->
                            @elseif($field['type'] === 'number')
                                <input 
                                    type="number" 
                                    id="input-{{ $field['id'] }}"
                                    wire:model="data.{{ $field['id'] }}"
                                    placeholder="{{ $field['placeholder'] }}"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">

                            <!-- DROPDOWN SELECT -->
                            @elseif($field['type'] === 'select')
                                <select 
                                    id="input-{{ $field['id'] }}"
                                    wire:model="data.{{ $field['id'] }}"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                                    <option value="">{{ $field['placeholder'] ?: 'Select an option' }}</option>
                                    @if(is_array($field['options']))
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    @endif
                                </select>

                            <!-- TEXTAREA -->
                            @elseif($field['type'] === 'textarea')
                                <textarea 
                                    id="input-{{ $field['id'] }}"
                                    wire:model="data.{{ $field['id'] }}"
                                    rows="4"
                                    placeholder="{{ $field['placeholder'] }}"
                                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"></textarea>

                            <!-- RADIO GROUP -->
                            @elseif($field['type'] === 'radio')
                                <div class="space-y-2 mt-2">
                                    @if(is_array($field['options']))
                                        @foreach($field['options'] as $idx => $option)
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input 
                                                    type="radio" 
                                                    name="radio-{{ $field['id'] }}"
                                                    wire:model="data.{{ $field['id'] }}"
                                                    value="{{ $option }}"
                                                    class="border-slate-800 bg-slate-950 text-violet-600 focus:ring-violet-500">
                                                <span class="text-sm text-slate-300">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>

                            <!-- CHECKBOX -->
                            @elseif($field['type'] === 'checkbox')
                                <div class="flex items-start mt-2">
                                    <label class="relative flex items-center gap-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            id="input-{{ $field['id'] }}"
                                            wire:model="data.{{ $field['id'] }}"
                                            class="rounded border-slate-800 bg-slate-950 text-violet-600 focus:ring-violet-500">
                                        <span class="text-sm text-slate-300 leading-normal">{{ $field['placeholder'] ?: 'Accept this term' }}</span>
                                    </label>
                                </div>
                            @endif

                            <!-- Error Message -->
                            @error('data.' . $field['id'])
                                <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-slate-900 flex justify-end">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 disabled:opacity-55 transition-all">
                            <span wire:loading.remove wire:target="submit" class="flex items-center gap-1.5">
                                Submit Response
                            </span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
