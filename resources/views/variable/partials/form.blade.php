<x-sendportal.text-field name="name" :label="__('Variable Name')" :value="$variable->name ?? null" />
<x-sendportal.text-field name="description" :label="__('Description')" :value="$variable->description ?? null" />

<x-sendportal.select-field name="value_type" :label="__('Value Type')" :options="$value_types" :value="$variable->value_type ?? old('value_type')" />
<x-sendportal.textarea-field name="value_from" :label="__('Value From')">{{ $variable->value_from ?? old('value_from') }}</x-sendportal.textarea-field>