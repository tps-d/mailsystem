<x-sendportal.text-field name="chat_id" :label="__('Chat ID')" :value="$socialuser->chat_id ?? null" />
<x-sendportal.text-field name="username" :label="__('User Name')" :value="$socialuser->username ?? null" />
<x-sendportal.text-field name="first_name" :label="__('First Name')" :value="$socialuser->first_name ?? null" />
<x-sendportal.text-field name="last_name" :label="__('Last Name')" :value="$socialuser->last_name ?? null" />
<x-sendportal.checkbox-field name="subscribed" :label="__('Subscribed')" :checked="empty($socialuser->unsubscribed_at)" />

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/css/bootstrap-select.min.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.12/dist/js/bootstrap-select.min.js"></script>
@endpush
