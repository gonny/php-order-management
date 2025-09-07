<script lang="ts">
	import { Select as SelectPrimitive } from "bits-ui";
	import type { ComponentProps } from "svelte";

	let {
		ref = $bindable(null),
		placeholder,
		...restProps
	}: ComponentProps<typeof SelectPrimitive.Value> & {
		placeholder?: string;
	} = $props();
</script>

<SelectPrimitive.Value
	bind:ref
	data-slot="select-value"
	{placeholder}
	{...restProps}
/>