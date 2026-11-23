@if (auth()->user()?->isCashier() && count($transitions) > 0)
    <section class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs">
        <h2 class="mb-4 text-lg font-semibold">Aksi Status</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ($transitions as $status)
                <form method="POST" action="{{ route('transactions.status', $transaction) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button class="rounded-xl px-4 py-2 text-sm font-semibold text-white {{ $status === 'cancelled' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-brand-600 hover:bg-brand-700' }}">{{ ucfirst($status) }}</button>
                </form>
            @endforeach
        </div>
    </section>
@endif