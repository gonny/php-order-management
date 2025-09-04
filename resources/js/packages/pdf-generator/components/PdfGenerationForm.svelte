<!--
	PDF Generation Form Component
	
	Main form component for PDF generation with image management,
	configuration options, and validation.
-->
<script lang="ts">
	import { toast } from 'svelte-sonner';
	import Button from '@/components/ui/button/button.svelte';
	import Label from '@/components/ui/label/label.svelte';
	import { usePdfGeneration } from '../hooks/use-pdf-generation';
	import { useImageUpload } from '../hooks/use-image-upload';
	import { validatePdfForm, getValidImages } from '../utils/pdf-helpers';
	import { PDF_CONFIG } from '../types/pdf';
	import ImageUploadGrid from './ImageUploadGrid.svelte';
	import PdfSettings from './PdfSettings.svelte';
	
	interface Props {
		orderId: string;
		onCancel?: () => void;
		onSuccess?: () => void;
		initialData?: {
			images?: string[];
			cellSize?: number;
			overlayUrl?: string;
		};
	}

	let { 
		orderId, 
		onCancel, 
		onSuccess,
		initialData = {}
	}: Props = $props();

	// Initialize form state
	let cellSize = $state(initialData.cellSize || PDF_CONFIG.defaultCellSize);
	let overlayUrl = $state(initialData.overlayUrl || '');

	// PDF generation hook
	const { generatePdf, isProcessing } = usePdfGeneration();

	// Image upload management
	const imageUpload = useImageUpload(initialData.images || ['']);

	/**
	 * Handles form submission
	 */
	async function handleSubmit(event: Event): Promise<void> {
		event.preventDefault();

		// Get current image URLs
		const imageUrls = imageUpload.getImageUrls();

		// Prepare form data
		const formData = {
			images: imageUrls,
			cell_size: cellSize,
			overlay_url: overlayUrl
		};

		// Validate form
		const validation = validatePdfForm(formData);
		if (!validation.isValid) {
			toast.error(validation.errors[0]);
			return;
		}

		try {
			await generatePdf(orderId, formData);
			onSuccess?.();
		} catch (error) {
			console.error('PDF generation failed:', error);
		}
	}

	/**
	 * Handles form cancellation
	 */
	function handleCancel(): void {
		onCancel?.();
	}

	/**
	 * Validates the current form state
	 */
	function validateCurrentForm(): boolean {
		const formData = {
			images: imageUpload.getImageUrls(),
			cell_size: cellSize,
			overlay_url: overlayUrl
		};

		return validatePdfForm(formData).isValid;
	}

	// Reactive validation
	let isFormValid = $derived(validateCurrentForm());
	let validImageCount = $derived(getValidImages(imageUpload.imageSlots.map(slot => slot.url)).length);
</script>

<form onsubmit={handleSubmit} class="space-y-6">
	<!-- PDF Settings -->
	<PdfSettings bind:cellSize bind:overlayUrl />

	<!-- Image Upload Grid -->
	<div class="space-y-4">
		<div class="flex items-center justify-between">
			<Label class="text-base font-medium">
				Image URLs ({validImageCount}/{PDF_CONFIG.maxImages} images)
			</Label>
			{#if imageUpload.canAddSlot}
				<Button 
					type="button" 
					variant="outline" 
					size="sm" 
					onclick={imageUpload.addSlot}
				>
					Add Image
				</Button>
			{/if}
		</div>

		<ImageUploadGrid 
			imageSlots={imageUpload.imageSlots}
			canRemoveSlot={imageUpload.canRemoveSlot}
			onUpdateSlot={imageUpload.updateSlot}
			onRemoveSlot={imageUpload.removeSlot}
			onValidateSlot={imageUpload.validateSlot}
		/>

		<p class="text-sm text-gray-500">
			Images should be 600×600px from Cloudflare R2. Empty slots will be left blank in the grid.
		</p>
	</div>

	<!-- Form Actions -->
	<div class="flex space-x-4">
		<Button
			type="submit"
			disabled={isProcessing || !isFormValid}
			class="bg-blue-600 hover:bg-blue-700"
		>
			{#if isProcessing}
				Generating PDF...
			{:else}
				Generate PDF
			{/if}
		</Button>

		{#if onCancel}
			<Button
				type="button"
				variant="outline"
				onclick={handleCancel}
				disabled={isProcessing}
			>
				Cancel
			</Button>
		{/if}
	</div>

	<!-- Form Status -->
	{#if !isFormValid}
		<div class="text-sm text-orange-600">
			{#if validImageCount === 0}
				Please add at least one image URL
			{:else if !overlayUrl.trim()}
				Please provide an overlay URL
			{:else if cellSize < PDF_CONFIG.minCellSize || cellSize > PDF_CONFIG.maxCellSize}
				Cell size must be between {PDF_CONFIG.minCellSize} and {PDF_CONFIG.maxCellSize} pixels
			{:else}
				Please check your form inputs
			{/if}
		</div>
	{:else if validImageCount > 0}
		<div class="text-sm text-green-600">
			Form is ready for PDF generation
		</div>
	{/if}
</form>