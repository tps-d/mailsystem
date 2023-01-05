<form action="{{ route('variable.destroy', $variable->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <a href="{{ route('variable.edit', $variable->id) }}"
       class="btn btn-sm btn-light">{{ __('Edit') }}</a>
    <button type="submit" class="btn btn-sm btn-light">{{ __('Delete') }}</button>
</form>
