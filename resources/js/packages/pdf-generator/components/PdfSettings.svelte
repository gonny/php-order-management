<!--
	PDF Settings Component
	
	Configuration component for PDF generation settings including
	cell size and overlay URL options.
-->
<script lang="ts">
	import Input from '@/components/ui/input/input.svelte';
	import Label from '@/components/ui/label/label.svelte';
	import { PDF_CONFIG } from '../types/pdf';
	import { validateOverlayUrl } from '../utils/image-validation';

	interface Props {
		cellSize: number;
		overlayUrl: string;
	}

	let { cellSize = $bindable(), overlayUrl = $bindable() }: Props = $props();

	// Validation state
	let overlayValidation = $state({ isValid: true, error: '' });

	/**
	 * Validates cell size input
	 */
	function validateCellSize(size: number): boolean {
		return size >= PDF_CONFIG.minCellSize && size <= PDF_CONFIG.maxCellSize;
	}

	/**
	 * Handles overlay URL validation
	 */
	function validateOverlay(): void {
		if (overlayUrl.trim() === '') {
			overlayValidation = { isValid: false, error: 'Overlay URL is required' };
			return;
		}

		const validation = validateOverlayUrl(overlayUrl);
		overlayValidation = {
			isValid: validation.isValid,
			error: validation.error || ''
		};
	}

	// Validate overlay URL when it changes
	$effect(() => {
		if (overlayUrl.trim() !== '') {
			validateOverlay();
		} else {
			overlayValidation = { isValid: false, error: 'Overlay URL is required' };
		}
	});

	// Reactive validation for cell size
	let isCellSizeValid = $derived(validateCellSize(cellSize));
</script>

<div class="space-y-4">
	<h3 class="text-lg font-medium text-gray-900">PDF Configuration</h3>
	
	<!-- Cell Size Setting -->
	<div class="space-y-2">
		<Label for="cell_size">Cell Size (pixels)</Label>
		<Input
			id="cell_size"
			type="number"
			min={PDF_CONFIG.minCellSize}
			max={PDF_CONFIG.maxCellSize}
			bind:value={cellSize}
			placeholder={String(PDF_CONFIG.defaultCellSize)}
			class={isCellSizeValid ? '' : 'border-red-500 focus:border-red-600 focus:ring-red-500'}
			required
		/>
		<div class="flex justify-between text-sm">
			<span class="text-gray-500">
				Size of each grid cell ({PDF_CONFIG.minCellSize}-{PDF_CONFIG.maxCellSize}px)
			</span>
			{#if !isCellSizeValid}
				<span class="text-red-600">
					Must be between {PDF_CONFIG.minCellSize}-{PDF_CONFIG.maxCellSize}px
				</span>
			{/if}
		</div>
	</div>

	<!-- Overlay URL Setting -->
	<div class="space-y-2">
		<Label for="overlay_url">Overlay URL</Label>
		<div class="relative">
			<Input
				id="overlay_url"
				type="url"
				bind:value={overlayUrl}
				placeholder="https://cdn.domain.com/overlays/cropmark.svg"
				class={overlayValidation.isValid ? '' : 'border-red-500 focus:border-red-600 focus:ring-red-500'}
				required
			/>
			
			<!-- Validation Indicator -->
			{#if overlayUrl.trim() !== ''}
				<div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
					{#if overlayValidation.isValid}
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
		
		<div class="flex justify-between text-sm">
			<span class="text-gray-500">
				PNG/SVG overlay to place over each image
			</span>
			{#if overlayValidation.error}
				<span class="text-red-600">
					{overlayValidation.error}
				</span>
			{/if}
		</div>
	</div>

	<!-- Configuration Preview -->
	<div class="mt-4 p-3 bg-blue-50 rounded-md">
		<h4 class="text-sm font-medium text-blue-900 mb-2">Configuration Summary</h4>
		<div class="text-sm text-blue-800 space-y-1">
			<div>• Grid Size: 3×3 cells</div>
			<div>• Cell Size: {cellSize}×{cellSize}px</div>
			<div>• Total Size: {cellSize * 3}×{cellSize * 3}px</div>
			{#if overlayValidation.isValid && overlayUrl.trim()}
				<div>• Overlay: ✓ Valid overlay URL</div>
			{:else}
				<div class="text-orange-800">• Overlay: ⚠ Overlay URL required</div>
			{/if}
		</div>
	</div>
</div>

