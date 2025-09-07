<script lang="ts">
	import { Select as SelectPrimitive } from "bits-ui";

	let {
		ref = $bindable(null),
		placeholder,
		...restProps
	}: any = $props();
</script>

<SelectPrimitive.Value
	bind:ref
	data-slot="select-value"
	{placeholder}
	{...restProps}
/>