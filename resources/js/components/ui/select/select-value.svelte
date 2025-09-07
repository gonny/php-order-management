<script lang="ts">
	let {
		ref = $bindable(null),
		placeholder,
		...restProps
	}: any = $props();
</script>

<span
	bind:this={ref}
	data-slot="select-value"
	{...restProps}
>
	{placeholder || "Select an option..."}
</span>