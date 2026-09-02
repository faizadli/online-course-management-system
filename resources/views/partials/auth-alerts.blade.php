@if (session('success'))
<div class="auth-alert auth-alert-success">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="auth-alert auth-alert-error">
    <ul class="space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
