<div>
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Dashboard</h1>
            <p class="mt-2 text-sm text-slate-400">Manage your dynamic forms, view response statistics, and analyze submissions.</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0 gap-3">
            <a href="{{ route('api-docs') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-800 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-300 shadow-sm hover:bg-slate-800 hover:text-white transition-all">
                API Docs
            </a>
            <a href="{{ route('forms.create') }}" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/25 transition-all hover:bg-violet-500 hover:shadow-violet-600/35">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Create New Form
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-400 flex items-center gap-3">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
        <!-- Stat Item 1 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-violet-600/10 blur-xl"></div>
            <p class="text-sm font-medium text-slate-400">Total Forms Created</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight text-white">{{ $stats['total_forms'] }}</span>
                <span class="text-xs text-violet-400 font-medium">forms active</span>
            </div>
        </div>

        <!-- Stat Item 2 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-indigo-600/10 blur-xl"></div>
            <p class="text-sm font-medium text-slate-400">Total Submissions</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight text-white">{{ $stats['total_submissions'] }}</span>
                <span class="text-xs text-indigo-400 font-medium">responses received</span>
            </div>
        </div>

        <!-- Stat Item 3 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md col-span-1 sm:col-span-2 lg:col-span-1">
            <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-emerald-600/10 blur-xl"></div>
            <p class="text-sm font-medium text-slate-400">Response Rate</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-4xl font-semibold tracking-tight text-white">
                    {{ $stats['total_forms'] > 0 ? round($stats['total_submissions'] / $stats['total_forms'], 1) : 0 }}
                </span>
                <span class="text-xs text-emerald-400 font-medium">avg submissions per form</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Forms Table (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 overflow-hidden backdrop-blur-md">
                <div class="border-b border-slate-900 p-6">
                    <h2 class="text-xl font-semibold text-white">Your Forms</h2>
                    <p class="mt-1 text-xs text-slate-400">A list of all dynamic forms created in your workspace.</p>
                </div>

                @if($forms->isEmpty())
                    <div class="flex flex-col items-center justify-center p-12 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900 text-slate-400 mb-4">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-white">No forms created yet</h3>
                        <p class="mt-1 text-xs text-slate-500">Get started by creating a new form manually, using AI, or importing a file.</p>
                        <div class="mt-6">
                            <a href="{{ route('forms.create') }}" class="inline-flex items-center rounded-xl bg-violet-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-violet-500">
                                Create your first form
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-900/50 bg-slate-950/20 text-xs font-semibold text-slate-400 uppercase">
                                    <th class="p-6">Form Details</th>
                                    <th class="p-6 text-center">Fields</th>
                                    <th class="p-6 text-center">Submissions</th>
                                    <th class="p-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/60">
                                @foreach($forms as $form)
                                    <tr class="hover:bg-slate-900/20 transition-colors group">
                                        <!-- Title / Info -->
                                        <td class="p-6">
                                            <div class="flex flex-col">
                                                <a href="{{ route('forms.submissions', $form->uuid) }}" class="font-semibold text-white hover:text-violet-400 transition-colors">
                                                    {{ $form->title }}
                                                </a>
                                                <span class="text-xs text-slate-500 mt-1 line-clamp-1 max-w-[280px]">
                                                    {{ $form->description ?? 'No description provided.' }}
                                                </span>
                                                <span class="text-[10px] text-slate-600 mt-2">
                                                    Created: {{ $form->created_at->format('M d, Y') }}
                                                </span>
                                            </div>
                                        </td>
                                        <!-- Fields count -->
                                        <td class="p-6 text-center">
                                            <span class="inline-flex items-center rounded-full bg-slate-900 px-2.5 py-1 text-xs font-medium text-slate-300">
                                                {{ is_array($form->fields) ? count($form->fields) : 0 }} fields
                                            </span>
                                        </td>
                                        <!-- Submissions count -->
                                        <td class="p-6 text-center">
                                            <span class="inline-flex items-center rounded-full bg-violet-600/10 px-2.5 py-1 text-xs font-medium text-violet-400 border border-violet-500/10">
                                                {{ $form->submissions_count }} submissions
                                            </span>
                                        </td>
                                        <!-- Actions -->
                                        <td class="p-6 text-right space-x-2">
                                            <div class="flex items-center justify-end gap-2">
                                                <!-- Public Link -->
                                                <a href="{{ route('forms.show', $form->uuid) }}" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-slate-400 hover:text-white transition-colors" title="View Public Form">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                                <!-- Submissions list -->
                                                <a href="{{ route('forms.submissions', $form->uuid) }}" class="inline-flex h-8 px-3 items-center justify-center rounded-lg border border-slate-800 bg-slate-900 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition-colors">
                                                    Responses
                                                </a>
                                                <!-- Delete -->
                                                <button type="button" 
                                                        wire:click="deleteForm({{ $form->id }})" 
                                                        wire:confirm="Are you sure you want to delete this form and all of its submissions?"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity Feed (Right Column) -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <h2 class="text-lg font-semibold text-white mb-4">Recent Submissions</h2>
                
                @if($stats['recent_submissions']->isEmpty())
                    <p class="text-xs text-slate-500 text-center py-6">No recent submission activity recorded.</p>
                @else
                    <ul role="list" class="space-y-4">
                        @foreach($stats['recent_submissions'] as $sub)
                            <li class="relative pl-4 border-l-2 border-violet-500/40 hover:border-violet-500 transition-colors">
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-white">
                                        {{ $sub->form->title }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">
                                        Submitted by {{ $sub->data['name'] ?? $sub->data['email'] ?? $sub->data['full_name'] ?? 'Anonymous' }}
                                    </span>
                                    <span class="text-[9px] text-slate-600 mt-1">
                                        {{ $sub->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- API Docs Info widget -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md relative overflow-hidden">
                <div class="absolute -right-12 -bottom-12 h-24 w-24 rounded-full bg-indigo-500/10 blur-xl"></div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">REST API Access</h2>
                <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                    Integrate your forms with external platforms using our secure REST APIs. Access form configurations, submit responses, and retrieve data securely.
                </p>
                <div class="mt-4">
                    <a href="{{ route('api-docs') }}" class="text-xs font-semibold text-violet-400 hover:text-violet-300 transition-colors flex items-center gap-1.5">
                        Read API Reference
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
