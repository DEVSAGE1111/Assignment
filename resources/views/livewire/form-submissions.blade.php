<div>
    <!-- Back Navigation & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-white transition-colors mb-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $form->title }} - Submissions</h1>
            <p class="text-xs text-slate-400 mt-1">Review user submissions, search data, export reports, or upload bulk files.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                type="button"
                onclick="navigator.clipboard.writeText('{{ route('forms.show', $form->uuid) }}'); alert('Form Link Copied!');"
                class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
                Copy Link
            </button>
            <a href="{{ route('forms.show', $form->uuid) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-violet-500">
                View Public Form
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if ($errorMessage)
        <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-400">
            {{ $errorMessage }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-400 flex items-center gap-3">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Import / Export Actions Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Submissions Controls (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                    <!-- Search Input -->
                    <div class="relative flex-grow max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search in submission data..."
                            class="w-full rounded-xl border border-slate-800 bg-slate-950/60 pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    </div>

                    <!-- Export Button -->
                    <button 
                        type="button" 
                        wire:click="exportToCsv"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-850 hover:text-white transition-all shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export to CSV
                    </button>
                </div>
            </div>

            <!-- Submissions Table Card -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 overflow-hidden backdrop-blur-md">
                @if($submissions->isEmpty())
                    <div class="flex flex-col items-center justify-center p-12 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-slate-500 mb-4">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-white">No submissions found</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ empty($search) ? 'This form has not received any submissions yet.' : 'No submissions match your search query.' }}
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-slate-900/50 bg-slate-950/20 text-xs font-semibold text-slate-400 uppercase">
                                    <th class="p-6">ID</th>
                                    <th class="p-6">Date</th>
                                    @foreach($fields as $field)
                                        <th class="p-6">{{ $field['label'] }}</th>
                                    @endforeach
                                    <th class="p-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/60 text-xs">
                                @foreach($submissions as $sub)
                                    <tr class="hover:bg-slate-900/20 transition-colors">
                                        <td class="p-6 font-mono text-[11px] text-slate-400">#{{ $sub->id }}</td>
                                        <td class="p-6 text-slate-400">{{ $sub->created_at->format('M d, Y H:i') }}</td>
                                        @foreach($fields as $field)
                                            <td class="p-6 text-white max-w-[200px] truncate" title="{{ $sub->data[$field['id']] ?? '' }}">
                                                {{ $sub->data[$field['id']] ?? '-' }}
                                            </td>
                                        @endforeach
                                        <td class="p-6 text-right">
                                            <button 
                                                type="button" 
                                                wire:click="deleteSubmission({{ $sub->id }})"
                                                wire:confirm="Are you sure you want to delete this response?"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Import Panel (Right Column) -->
        <div class="space-y-6">
            <!-- File Import Box -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <h2 class="text-lg font-semibold text-white mb-2">Import Responses</h2>
                <p class="text-xs text-slate-400 mb-6">
                    Bulk upload responses from spreadsheet files (XLSX, CSV) or import a single filled questionnaire from Word (DOCX).
                </p>

                <form wire:submit.prevent="importResponses" class="space-y-4">
                    <div class="flex justify-center rounded-xl border border-dashed border-slate-800 bg-slate-950/20 py-6 transition-colors hover:border-slate-700">
                        <div class="text-center">
                            <svg class="mx-auto h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                            </svg>
                            <div class="mt-2 text-xs text-slate-400">
                                <label for="response-file-upload" class="relative cursor-pointer rounded-md font-semibold text-violet-400 focus-within:outline-none hover:text-violet-300">
                                    <span>Upload response file</span>
                                    <input id="response-file-upload" type="file" wire:model="uploadedFile" class="sr-only">
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-600">XLSX, CSV, or DOCX up to 5MB</p>

                            @if ($uploadedFile)
                                <div class="mt-3 text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 rounded-md py-1 px-2 border border-emerald-500/20 truncate max-w-[200px]">
                                    {{ $uploadedFile->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('uploadedFile') <span class="text-[11px] text-red-400 mt-1 block">{{ $message }}</span> @enderror

                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white shadow-md hover:bg-violet-500 disabled:opacity-55 transition-all">
                        <span wire:loading.remove wire:target="importResponses">Import File</span>
                        <span wire:loading wire:target="importResponses" class="flex items-center gap-1">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Import Help Guide -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Response Import Mapping</h3>
                <div class="space-y-4 text-xs text-slate-400 leading-relaxed">
                    <div>
                        <strong class="text-white block mb-0.5">Spreadsheets (CSV/XLSX)</strong>
                        Bulk import multiple responses. Row headers must match the <strong class="text-white">Field Label</strong> or the <strong class="text-white">Field ID Slug</strong>.
                    </div>
                    <div>
                        <strong class="text-white block mb-0.5">Word Documents (DOCX)</strong>
                        Import a single submission from a questionnaire. Write the text freely or format it like "Question: Answer". Our AI will extract values for each field.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
