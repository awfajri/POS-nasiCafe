@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm text-emerald-950 shadow-xs" role="alert">
        <div class="flex items-center gap-2.5">
            <x-lucide-check-circle-2 class="w-5 h-5 text-emerald-600 shrink-0" />
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <button @click="show = false" type="button" class="p-1 rounded-lg text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900 transition" aria-label="Tutup">
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>
@endif

@if (session('error') || (isset($errors) && $errors->any()))
    <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-start justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-950 shadow-xs" role="alert">
        <div class="flex items-start gap-2.5">
            <x-lucide-alert-circle class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div>
                @if (session('error'))
                    <p class="font-medium">{{ session('error') }}</p>
                @endif
                @if (isset($errors) && $errors->any())
                    <ul class="list-disc pl-4 space-y-1 {{ session('error') ? 'mt-2' : '' }}">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <button @click="show = false" type="button" class="p-1 rounded-lg text-rose-700 hover:bg-rose-100 hover:text-rose-900 transition" aria-label="Tutup">
            <x-lucide-x class="w-4 h-4" />
        </button>
    </div>
@endif
