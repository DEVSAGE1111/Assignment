<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Developer API Reference</h1>
        <p class="mt-2 text-sm text-slate-400">Integrate Formify AI with your existing applications. We provide secure REST endpoints for fetching forms, registering submissions, and retrieving response data.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- API Specs (Left 2 Columns) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Authentication Header Section -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <h2 class="text-xl font-semibold text-white mb-2">Authentication</h2>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">
                    Public endpoints (fetching form structures and submitting user responses) do not require authentication.
                    However, endpoints for retrieving submission databases require passing your API secret token in the header:
                </p>
                <div class="bg-slate-950 rounded-xl p-4 border border-slate-900 font-mono text-xs text-slate-300">
                    <span class="text-violet-400">X-API-Key</span>: {{ $apiToken }}
                </div>
                <p class="text-[10px] text-slate-500 mt-3">
                    ℹ️ You can change this token by setting <code class="text-slate-300">API_SECRET_TOKEN</code> in your server's <code class="text-slate-300">.env</code> file.
                </p>
            </div>

            <!-- Endpoint 1: Get Form Schema -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">GET</span>
                    <span class="font-mono text-sm font-semibold text-white">/api/forms/{uuid}</span>
                </div>
                <p class="text-xs text-slate-400 mb-6">Retrieve the structured JSON schema of a form containing field labels, input types, validation requirements, and options.</p>

                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Example Response</h3>
                <pre class="bg-slate-950 rounded-xl p-4 border border-slate-900 font-mono text-[11px] text-slate-300 overflow-x-auto"><code>{
  "status": "success",
  "data": {
    "title": "Contact Us",
    "description": "Get in touch with support.",
    "fields": [
      {
        "id": "full_name",
        "type": "text",
        "label": "Full Name",
        "placeholder": "John Doe",
        "required": true
      }
    ]
  }
}</code></pre>
            </div>

            <!-- Endpoint 2: Submit Response -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center rounded-lg bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-400 border border-indigo-500/20">POST</span>
                    <span class="font-mono text-sm font-semibold text-white">/api/forms/{uuid}/submit</span>
                </div>
                <p class="text-xs text-slate-400 mb-6">Submit data responses to a form. Field keys must correspond to the form field IDs. Input is validated dynamically based on the form's config.</p>

                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Request Body (JSON)</h3>
                <pre class="bg-slate-950 rounded-xl p-4 border border-slate-900 font-mono text-[11px] text-slate-300 overflow-x-auto mb-6"><code>{
  "data": {
    "full_name": "Alice Cooper",
    "email": "alice@example.com"
  }
}</code></pre>

                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Example Success Response</h3>
                <pre class="bg-slate-950 rounded-xl p-4 border border-slate-900 font-mono text-[11px] text-slate-300 overflow-x-auto"><code>{
  "status": "success",
  "message": "Response submitted successfully."
}</code></pre>
            </div>

            <!-- Endpoint 3: Fetch Responses -->
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20">GET</span>
                    <span class="font-mono text-sm font-semibold text-white">/api/forms/{uuid}/submissions</span>
                </div>
                <p class="text-xs text-slate-400 mb-6">
                    Retrieve a list of all responses submitted to this form. 
                    <span class="text-amber-400">Requires X-API-Key header.</span>
                </p>

                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Example Response</h3>
                <pre class="bg-slate-950 rounded-xl p-4 border border-slate-900 font-mono text-[11px] text-slate-300 overflow-x-auto"><code>{
  "status": "success",
  "data": [
    {
      "submission_id": 12,
      "submitted_at": "2026-08-06 11:24:00",
      "data": {
        "full_name": "Alice Cooper",
        "email": "alice@example.com"
      }
    }
  ]
}</code></pre>
            </div>
        </div>

        <!-- Code Snippets / Integration Guides (Right Column) -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-900 bg-slate-950/40 p-6 backdrop-blur-md sticky top-24">
                <h2 class="text-lg font-semibold text-white mb-4">Code Integration</h2>
                
                <div class="space-y-6">
                    <!-- cURL Example -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-300 mb-2">cURL: Fetch Submissions</h4>
                        <pre class="bg-slate-950 rounded-lg p-3 border border-slate-900 font-mono text-[10px] text-slate-400 overflow-x-auto"><code>curl -X GET "{{ url('/') }}/api/forms/FORM_UUID/submissions" \
  -H "X-API-Key: {{ $apiToken }}" \
  -H "Accept: application/json"</code></pre>
                    </div>

                    <!-- Javascript Fetch -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-300 mb-2">JavaScript: Submit Data</h4>
                        <pre class="bg-slate-950 rounded-lg p-3 border border-slate-900 font-mono text-[10px] text-slate-400 overflow-x-auto"><code>fetch('{{ url('/') }}/api/forms/FORM_UUID/submit', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    data: {
      full_name: 'Jane Doe',
      email: 'jane@example.com'
    }
  })
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                    </div>

                    <!-- PHP Laravel Guzzle -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-300 mb-2">PHP: Fetch Schema</h4>
                        <pre class="bg-slate-950 rounded-lg p-3 border border-slate-900 font-mono text-[10px] text-slate-400 overflow-x-auto"><code>use Illuminate\Support\Facades\Http;

$response = Http::acceptJson()
    ->get('{{ url('/') }}/api/forms/FORM_UUID');

$schema = $response->json();</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
