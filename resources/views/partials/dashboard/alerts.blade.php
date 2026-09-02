@if (session('success'))
<div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
    <ul class="space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
