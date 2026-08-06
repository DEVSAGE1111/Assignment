<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Create Form</h1>
        <p class="mt-2 text-sm text-slate-400">Design a form using our interactive manual builder, generate one with AI, or import a structure from Excel/Word.</p>
    </div>

    <!-- Mode Selector Tabs -->
    <div class="border-b border-slate-800 mb-8">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button 
                type="button"
                wire:click="$set('generationMode', 'manual')"
                class="border-b-2 py-4 px-1 text-sm font-medium transition-all {{ $generationMode === 'manual' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-300' }}">
                Manual Builder
            </button>
            <button 
                type="button"
                wire:click="$set('generationMode', 'ai')"
                class="border-b-2 py-4 px-1 text-sm font-medium transition-all {{ $generationMode === 'ai' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-300' }}">
                AI Generator
            </button>
            <button 
                type="button"
                wire:click="$set('generationMode', 'import')"
                class="border-b-2 py-4 px-1 text-sm font-medium transition-all {{ $generationMode === 'import' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:border-slate-700 hover:text-slate-300' }}">
                Word/Excel Import
            </button>
        </nav>
    </div>

    <!-- Feedback Alerts -->
    @if ($errorMessage)
        <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-400">
            {{ $errorMessage }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-400">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-6 rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-400 flex items-center gap-3">
            <svg class="h-5 w-5 shrink-0 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.2-.17-1.516-2.625l6.28-10.875zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ session('warning') }}
        </div>
    @endif

    <!-- 1. AI PROMPT BUILDER PANEL -->
    @if($generationMode === 'ai')
        <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md mb-8">
            <h2 class="text-xl font-semibold text-white mb-2">Generate with AI</h2>
            <p class="text-xs text-slate-400 mb-6">Describe the form you want to create (e.g. fields, target audience, layout) and our AI will build the initial structure.</p>

            <form wire:submit.prevent="generateFromAi" class="space-y-4">
                <div>
                    <label for="aiPrompt" class="block text-sm font-medium text-slate-300 mb-2">Describe your form</label>
                    <textarea 
                        id="aiPrompt"
                        wire:model="aiPrompt" 
                        rows="4" 
                        placeholder="Create a job application form with Full Name, Email, Phone, Position applying for (Select menu: Developer, Designer, Manager), and a Cover Letter textarea..."
                        class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"></textarea>
                    @error('aiPrompt') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-violet-500 disabled:opacity-55 disabled:cursor-not-allowed transition-all">
                        <span wire:loading.remove wire:target="generateFromAi" class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Generate Form Structure
                        </span>
                        <span wire:loading wire:target="generateFromAi" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            AI Generating (this may take a few seconds)...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- 2. DOCUMENT IMPORT PANEL -->
    @if($generationMode === 'import')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Upload Box (Left 2 Columns) -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <h2 class="text-xl font-semibold text-white mb-2">Import from Document</h2>
                <p class="text-xs text-slate-400 mb-6">Upload an Excel spreadsheet containing column definitions, or upload a Word document containing questions to generate the form.</p>

                <form wire:submit.prevent="importFromFile" class="space-y-6">
                    <div class="flex justify-center rounded-xl border-2 border-dashed border-slate-800 bg-slate-950/20 px-6 py-10 transition-colors hover:border-slate-700">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <div class="mt-4 flex text-sm leading-6 text-slate-400 justify-center">
                                <label for="file-upload" class="relative cursor-pointer rounded-md font-semibold text-violet-400 focus-within:outline-none focus-within:ring-2 focus-within:ring-violet-600 focus-within:ring-offset-2 hover:text-violet-300">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file-upload" type="file" wire:model="uploadedFile" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs leading-5 text-slate-500">Excel (.xlsx), CSV (.csv), or Word (.docx) up to 5MB</p>
                            
                            @if ($uploadedFile)
                                <div class="mt-4 flex items-center justify-center gap-1.5 text-xs text-emerald-400 font-semibold bg-emerald-500/10 rounded-lg py-1 px-3 border border-emerald-500/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $uploadedFile->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('uploadedFile') <span class="text-xs text-red-400 mt-1 block text-center">{{ $message }}</span> @enderror

                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-violet-500 disabled:opacity-55 disabled:cursor-not-allowed transition-all">
                            <span wire:loading.remove wire:target="importFromFile" class="flex items-center gap-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Parse and Import Fields
                            </span>
                            <span wire:loading wire:target="importFromFile" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Parsing file...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Import Guidelines (Right Column) -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Spreadsheet Guidelines</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">
                        To import form fields from Excel or CSV, your file must include these headers in the first row:
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400 mb-6 list-disc list-inside">
                        <li><strong class="text-white">Label</strong>: Name of the field (required)</li>
                        <li><strong class="text-white">Type</strong>: text, number, email, select, radio, checkbox, textarea</li>
                        <li><strong class="text-white">Required</strong>: true/yes or false/no</li>
                        <li><strong class="text-white">Placeholder</strong>: Prompt text inside input</li>
                        <li><strong class="text-white">Options</strong>: Comma-separated options (for select/radio)</li>
                    </ul>
                    <a href="/downloads/form_template.csv" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/60 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-850 hover:text-white transition-colors" download>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download CSV Template
                    </a>
                </div>

                <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Word Guidelines</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">
                        To generate fields from a Word document (.docx), upload a questionnaire outline or brief prompt. The AI parser will automatically generate structured fields based on the document text.
                    </p>
                    <a href="/downloads/sample_form_prompt.docx" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/60 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-850 hover:text-white transition-colors" download>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Word Sample
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. INTERACTIVE FORM CONFIGURATION / EDITOR PANEL -->
    <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
        <div class="flex items-center justify-between border-b border-slate-900 pb-6 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-white">Form Details & Field Mapping</h2>
                <p class="text-xs text-slate-400 mt-1">Refine and edit your form schema before final saving.</p>
            </div>
            <button 
                type="button"
                wire:click="saveForm"
                class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 transition-all">
                Save Form
            </button>
        </div>

        <div class="space-y-6">
            <!-- Title & Description -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label for="form-title" class="block text-sm font-semibold text-slate-200">Form Title</label>
                    <p class="text-xs text-slate-500 mt-1">Give your form a meaningful name.</p>
                </div>
                <div class="md:col-span-2">
                    <input 
                        type="text" 
                        id="form-title"
                        wire:model="title" 
                        placeholder="e.g. Customer Satisfaction Survey"
                        class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    @error('title') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label for="form-desc" class="block text-sm font-semibold text-slate-200">Description</label>
                    <p class="text-xs text-slate-500 mt-1">Briefly explain the purpose of this form.</p>
                </div>
                <div class="md:col-span-2">
                    <textarea 
                        id="form-desc"
                        wire:model="description" 
                        rows="2" 
                        placeholder="e.g. Please take 2 minutes to fill out this survey."
                        class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500"></textarea>
                    @error('description') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="border-slate-900 my-6">

            <!-- Dynamic Fields List -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-6 flex items-center justify-between">
                    Form Fields
                    <button 
                        type="button" 
                        wire:click="addField" 
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-800 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Add Field
                    </button>
                </h3>

                @if(empty($fields))
                    <p class="text-xs text-slate-500 text-center py-6">No fields added yet. Click "Add Field" to start building.</p>
                @else
                    <div class="space-y-4">
                        @foreach($fields as $index => $field)
                            <div class="rounded-xl border border-slate-900 bg-slate-950/60 p-4 relative group" wire:key="field-{{ $field['id'] }}">
                                <!-- Header Actions inside field card -->
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-1.5">
                                        <!-- Order buttons -->
                                        <button 
                                            type="button" 
                                            wire:click="moveFieldUp({{ $index }})" 
                                            class="text-slate-500 hover:text-slate-300 disabled:opacity-30" 
                                            {{ $index === 0 ? 'disabled' : '' }}>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="moveFieldDown({{ $index }})" 
                                            class="text-slate-500 hover:text-slate-300 disabled:opacity-30" 
                                            {{ $index === count($fields) - 1 ? 'disabled' : '' }}>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Field #{{ $index + 1 }}</span>
                                    </div>
                                    
                                    <button 
                                        type="button" 
                                        wire:click="removeField({{ $index }})" 
                                        class="text-red-500/80 hover:text-red-400 text-xs flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        Remove
                                    </button>
                                </div>

                                <!-- Inputs Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <!-- Label -->
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Field Label</label>
                                        <input 
                                            type="text" 
                                            wire:model="fields.{{ $index }}.label" 
                                            placeholder="e.g. Phone Number"
                                            class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none">
                                        @error("fields.{$index}.label") <span class="text-[10px] text-red-400 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Type -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Input Type</label>
                                        <select 
                                            wire:change="changeFieldType({{ $index }}, $event.target.value)"
                                            class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white focus:border-violet-500 focus:outline-none">
                                            <option value="text" {{ $field['type'] === 'text' ? 'selected' : '' }}>Text Input</option>
                                            <option value="number" {{ $field['type'] === 'number' ? 'selected' : '' }}>Number</option>
                                            <option value="email" {{ $field['type'] === 'email' ? 'selected' : '' }}>Email</option>
                                            <option value="select" {{ $field['type'] === 'select' ? 'selected' : '' }}>Dropdown Select</option>
                                            <option value="radio" {{ $field['type'] === 'radio' ? 'selected' : '' }}>Radio Group</option>
                                            <option value="checkbox" {{ $field['type'] === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                            <option value="textarea" {{ $field['type'] === 'textarea' ? 'selected' : '' }}>Text Area</option>
                                        </select>
                                    </div>

                                    <!-- Placeholder / Options details -->
                                    <div class="md:col-span-3">
                                        @if(in_array($field['type'], ['select', 'radio']))
                                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Options Mapping</label>
                                            <div class="space-y-1.5">
                                                @if(is_array($field['options']))
                                                    @foreach($field['options'] as $optIdx => $opt)
                                                        <div class="flex items-center gap-1">
                                                            <input 
                                                                type="text" 
                                                                wire:model="fields.{{ $index }}.options.{{ $optIdx }}" 
                                                                class="w-full rounded-md border border-slate-850 bg-slate-950 px-2 py-1 text-[10px] text-white focus:border-violet-500 focus:outline-none">
                                                            <button 
                                                                type="button" 
                                                                wire:click="removeOption({{ $index }}, {{ $optIdx }})" 
                                                                class="text-red-500 hover:text-red-400">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7" /></svg>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <button 
                                                    type="button" 
                                                    wire:click="addOption({{ $index }})" 
                                                    class="inline-flex items-center text-[10px] text-violet-400 hover:text-violet-300 font-semibold gap-1 mt-1">
                                                    + Add Option
                                                </button>
                                            </div>
                                        @else
                                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Placeholder</label>
                                            <input 
                                                type="text" 
                                                wire:model="fields.{{ $index }}.placeholder" 
                                                placeholder="e.g. Enter phone number"
                                                class="w-full rounded-lg border border-slate-800 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none">
                                        @endif
                                    </div>

                                    <!-- Required status -->
                                    <div class="md:col-span-2 flex items-center pt-5">
                                        <label class="relative flex items-center gap-2 cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                wire:model="fields.{{ $index }}.required" 
                                                class="rounded border-slate-800 bg-slate-950 text-violet-600 focus:ring-violet-500">
                                            <span class="text-xs font-medium text-slate-300">Required</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-slate-900 mt-8">
                <button 
                    type="button" 
                    wire:click="addField"
                    class="inline-flex items-center gap-1 rounded-xl border border-slate-850 bg-slate-900/60 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
                    + Add More Fields
                </button>

                <button 
                    type="button"
                    wire:click="saveForm"
                    class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 hover:bg-violet-500 hover:shadow-violet-600/35 transition-all">
                    Save Form
                </button>
            </div>
        </div>
    </div>
</div>
