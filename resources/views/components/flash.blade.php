@if (session('status'))
    <div role="status"
        class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M16.704 5.296a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 011.414-1.414L8.5 12.086l6.793-6.79a1 1 0 011.411 0z"
                clip-rule="evenodd" />
        </svg>
        <p>{{ session('status') }}</p>
    </div>
@endif