@props(['name'])

@error($name)
<p class="field-error" data-server-error>{{ $message }}</p>
@enderror
