<!--
	Image Upload Grid Component
	
	Grid component for managing multiple image URL inputs with
	validation feedback and remove functionality.
-->
<script lang="ts">
	import Button from '@/components/ui/button/button.svelte';
	import Input from '@/components/ui/input/input.svelte';
	import type { ImageSlot } from '../types/pdf';
	import { generatePlaceholderUrl } from '../utils/pdf-helpers';

	interface Props {
		imageSlots: ImageSlot[];
		canRemoveSlot: boolean;
		onUpdateSlot: (index: number, url: string) => void;
		onRemoveSlot: (index: number) => void;
		onValidateSlot: (index: number) => Promise<void>;
	}

	let { 
		imageSlots, 
		canRemoveSlot, 
		onUpdateSlot, 
		onRemoveSlot, 
		onValidateSlot 
	}: Props = $props();

	/**
	 * Handles URL input change with validation
	 */
	async function handleUrlChange(index: number, event: Event): Promise<void> {
		const target = event.target as HTMLInputElement;
		if (!target) return;

		const newUrl = target.value;
		onUpdateSlot(index, newUrl);

		// Validate after a short delay to avoid excessive API calls
		setTimeout(() => {
			onValidateSlot(index);
		}, 500);
	}

	/**
	 * Handles remove button click
	 */
	function handleRemove(index: number): void {
		if (canRemoveSlot) {
			onRemoveSlot(index);
		}
	}

	/**
	 * Generates placeholder text for input
	 */
	function getPlaceholder(index: number): string {
		return generatePlaceholderUrl(index);
	}

	/**
	 * Gets validation classes for input styling
	 */
	function getValidationClasses(slot: ImageSlot): string {
		if (slot.url.trim() === '') {
			return ''; // Default styling for empty slots
		}
		
		return slot.isValid 
			? 'border-green-500 focus:border-green-600 focus:ring-green-500' 
			: 'border-red-500 focus:border-red-600 focus:ring-red-500';
	}
</script>

<div class="space-y-3">
	{#each imageSlots as slot, index (slot.id)}
		<div class="flex items-center space-x-2">
			<!-- Image URL Input -->
			<div class="flex-1 relative">
				<Input
					type="url"
					value={slot.url}
					placeholder={getPlaceholder(index)}
					class="w-full {getValidationClasses(slot)}"
					onchange={(event) => handleUrlChange(index, event)}
				/>
				
				<!-- Validation Indicator -->
				{#if slot.url.trim() !== ''}
					<div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
						{#if slot.isValid}
							<svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
							</svg>
						{:else}
							<svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
								<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
							</svg>
						{/if}
					</div>
				{/if}
			</div>

			<!-- Remove Button -->
			{#if canRemoveSlot}
				<Button
					type="button"
					variant="outline"
					size="sm"
					onclick={() => handleRemove(index)}
					class="shrink-0"
				>
					Remove
				</Button>
			{/if}
		</div>

		<!-- Validation Error Message -->
		{#if slot.url.trim() !== '' && !slot.isValid}
			<p class="text-sm text-red-600 ml-2">
				Invalid image URL format or inaccessible image
			</p>
		{/if}
	{/each}
</div>

<!-- Grid Layout Preview -->
{#if imageSlots.length > 0}
	<div class="mt-4 p-4 bg-gray-50 rounded-lg">
		<h4 class="text-sm font-medium text-gray-700 mb-2">Grid Preview</h4>
		<div class="grid grid-cols-3 gap-2">
			{#each Array.from({ length: 9 }, (_, i) => i) as gridIndex (gridIndex)}
				<div class="aspect-square border-2 border-dashed border-gray-300 rounded flex items-center justify-center text-xs text-gray-500">
					{#if gridIndex < imageSlots.length && imageSlots[gridIndex]?.url.trim()}
						{#if imageSlots[gridIndex].isValid}
							<span class="text-green-600">✓ Image {gridIndex + 1}</span>
						{:else}
							<span class="text-red-600">✗ Invalid {gridIndex + 1}</span>
						{/if}
					{:else}
						<span>Empty {gridIndex + 1}</span>
					{/if}
				</div>
			{/each}
		</div>
	</div>
{/if}

