/**
 * PDF Generator Package
 * 
 * Modular package for PDF generation functionality including
 * form management, image upload, validation, and API integration.
 */

// Components
export { default as PdfGenerationForm } from './components/PdfGenerationForm.svelte';
export { default as ImageUploadGrid } from './components/ImageUploadGrid.svelte';
export { default as PdfSettings } from './components/PdfSettings.svelte';
export { default as PdfPreview } from './components/PdfPreview.svelte';

// Hooks
export { usePdfGeneration, usePdfStatus } from './hooks/use-pdf-generation';
export { useImageUpload, useImagePlaceholders, useBatchImageOperations } from './hooks/use-image-upload';

// Types
export type {
	PdfGenerationForm as PdfGenerationFormData,
	PdfGenerationRequest,
	PdfGenerationResponse,
	PdfGenerationError,
	ImageSlot,
	PdfConfiguration,
	PdfSpecifications
} from './types/pdf';
export { PDF_CONFIG, PDF_SPECS } from './types/pdf';

// Utils
export {
	validatePdfForm,
	isValidUrl,
	isValidImageUrl,
	getValidImages,
	createImageSlots,
	addImageSlot,
	removeImageSlot,
	updateImageUrl,
	generatePlaceholderUrl,
	formatErrorMessage,
	createPdfFilename
} from './utils/pdf-helpers';

export {
	validateImageUrl,
	validateImageUrls,
	isCloudflareR2Url,
	getImageFilename,
	validateImageDimensions,
	validateOverlayUrl,
	validateAllAssets
} from './utils/image-validation';