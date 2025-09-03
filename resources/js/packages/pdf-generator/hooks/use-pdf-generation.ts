/**
 * PDF Generation Hook
 * 
 * Provides PDF generation functionality including API calls,
 * state management, and error handling.
 */

import { toast } from 'svelte-sonner';
import { router } from '@inertiajs/svelte';
import type { 
	PdfGenerationForm, 
	PdfGenerationRequest, 
	PdfGenerationResponse, 
	PdfGenerationError 
} from '../types/pdf';
import { validatePdfForm, getValidImages, formatErrorMessage } from '../utils/pdf-helpers';

export interface UsePdfGenerationReturn {
	generatePdf: (orderId: string, form: PdfGenerationForm) => Promise<void>;
	downloadPdf: (orderId: string, orderNumber: string) => void;
	isProcessing: boolean;
}

/**
 * Hook for managing PDF generation operations
 */
export function usePdfGeneration(): UsePdfGenerationReturn {
	let isProcessing = $state(false);

	/**
	 * Generates a PDF for the given order
	 */
	async function generatePdf(orderId: string, form: PdfGenerationForm): Promise<void> {
		// Validate form data
		const validation = validatePdfForm(form);
		if (!validation.isValid) {
			toast.error(validation.errors[0]);
			return;
		}

		isProcessing = true;

		try {
			// Prepare request data
			const requestData: PdfGenerationRequest = {
				images: getValidImages(form.images),
				cell_size: form.cell_size,
				overlay_url: form.overlay_url
			};

			// Make API request
			const response = await fetch(`/api/v1/orders/${orderId}/pdf`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					// Note: HMAC headers would be added by middleware/interceptor
				},
				body: JSON.stringify(requestData)
			});

			if (response.ok) {
				const data: PdfGenerationResponse = await response.json();
				toast.success(data.message || 'PDF generation started! Check back in a few moments.');
				
				// Reload the page after successful generation
				setTimeout(() => {
					router.reload();
				}, 2000);
			} else {
				const error: PdfGenerationError = await response.json();
				const errorMessage = error.message || 'Failed to generate PDF';
				toast.error(errorMessage);

				// Handle validation errors
				if (error.errors) {
					Object.values(error.errors).flat().forEach(msg => {
						toast.error(msg);
					});
				}
			}
		} catch (error) {
			console.error('PDF generation error:', error);
			const errorMessage = formatErrorMessage(error);
			toast.error(`Failed to generate PDF: ${errorMessage}`);
		} finally {
			isProcessing = false;
		}
	}

	/**
	 * Downloads the generated PDF
	 */
	function downloadPdf(orderId: string): void {
		try {
			// Create a download link
			const downloadUrl = `/orders/${orderId}/pdf`;
			
			// Option 1: Direct navigation (current implementation)
			window.location.href = downloadUrl;
			
			// Option 2: Programmatic download (alternative)
			// const link = document.createElement('a');
			// link.href = downloadUrl;
			// link.download = `order-${orderNumber}.pdf`;
			// document.body.appendChild(link);
			// link.click();
			// document.body.removeChild(link);
			
			toast.success('PDF download started');
		} catch (error) {
			console.error('PDF download error:', error);
			toast.error('Failed to download PDF');
		}
	}

	return {
		generatePdf,
		downloadPdf,
		get isProcessing() {
			return isProcessing;
		}
	};
}

/**
 * Hook for checking PDF generation status
 */
export function usePdfStatus() {
	let isChecking = $state(false);

	/**
	 * Checks if a PDF exists for the given order
	 */
	async function checkPdfStatus(orderId: string): Promise<boolean> {
		isChecking = true;
		
		try {
			const response = await fetch(`/api/v1/orders/${orderId}`, {
				method: 'GET',
				headers: {
					'Accept': 'application/json',
				}
			});

			if (response.ok) {
				const data = await response.json();
				return data.data?.pdf_exists || false;
			}
			
			return false;
		} catch (error) {
			console.error('PDF status check error:', error);
			return false;
		} finally {
			isChecking = false;
		}
	}

	return {
		checkPdfStatus,
		get isChecking() {
			return isChecking;
		}
	};
}