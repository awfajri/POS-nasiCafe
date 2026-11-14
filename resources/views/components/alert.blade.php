@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-status-success/30 bg-status-success/10 px-4 py-3 text-sm text-ink-primary" role="alert">
        <span class="font-medium">{{ session('success') }}</span>
        <button @click="show = false" type="button" class="text-ink-muted hover:text-ink-primary text-lg leading-none" aria-label="Tutup">&times;</button>
    </div>
@endif

@if (session('error') || $errors->any())
    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-status-danger/30 bg-status-danger/10 px-4 py-3 text-sm text-ink-primary" role="alert">
        <div>
            @if (session('error'))
                <p class="font-medium">{{ session('error') }}</p>
            @endif
            @if ($errors->any())
                <ul class="list-disc pl-4 space-y-1 {{ session('error') ? 'mt-2' : '' }}">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <button @click="show = false" type="button" class="text-ink-muted hover:text-ink-primary text-lg leading-none" aria-label="Tutup">&times;</button>
    </div>
@endif
