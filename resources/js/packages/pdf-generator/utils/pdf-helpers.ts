/**
 * PDF Generator Package Utilities
 * 
 * Business logic utilities for PDF generation including
 * validation, formatting, and helper functions.
 */

import type { PdfGenerationForm, ImageSlot } from '../types/pdf';
import { PDF_CONFIG } from '../types/pdf';

/**
 * Validates a PDF generation form
 */
export function validatePdfForm(form: PdfGenerationForm): {
	isValid: boolean;
	errors: string[];
} {
	const errors: string[] = [];

	// Validate images
	const validImages = form.images.filter(img => img.trim() !== '');
	if (validImages.length === 0) {
		errors.push('Please add at least one image URL');
	}
	if (validImages.length > PDF_CONFIG.maxImages) {
		errors.push(`Maximum ${PDF_CONFIG.maxImages} images allowed`);
	}

	// Validate cell size
	if (form.cell_size < PDF_CONFIG.minCellSize || form.cell_size > PDF_CONFIG.maxCellSize) {
		errors.push(`Cell size must be between ${PDF_CONFIG.minCellSize} and ${PDF_CONFIG.maxCellSize} pixels`);
	}

	// Validate overlay URL
	if (!form.overlay_url.trim()) {
		errors.push('Please provide an overlay URL');
	} else if (!isValidUrl(form.overlay_url)) {
		errors.push('Please provide a valid overlay URL');
	}

	// Validate image URLs
	validImages.forEach((url, index) => {
		if (!isValidUrl(url)) {
			errors.push(`Image ${index + 1} has an invalid URL`);
		}
	});

	return {
		isValid: errors.length === 0,
		errors
	};
}

/**
 * Validates if a string is a valid URL
 */
export function isValidUrl(url: string): boolean {
	try {
		new URL(url);
		return true;
	} catch {
		return false;
	}
}

/**
 * Validates if an image URL has a valid format
 */
export function isValidImageUrl(url: string): boolean {
	if (!isValidUrl(url)) return false;
	
	const validExtensions = PDF_CONFIG.allowedImageFormats;
	const urlLower = url.toLowerCase();
	
	return validExtensions.some(ext => urlLower.includes(`.${ext}`));
}

/**
 * Filters out empty image URLs from form data
 */
export function getValidImages(images: string[]): string[] {
	return images.filter(img => img.trim() !== '');
}

/**
 * Creates image slots for the form interface
 */
export function createImageSlots(images: string[]): ImageSlot[] {
	return images.map((url, index) => ({
		id: index,
		url,
		isValid: url.trim() === '' || isValidImageUrl(url)
	}));
}

/**
 * Adds an empty image slot to the form
 */
export function addImageSlot(currentImages: string[]): string[] {
	if (currentImages.length >= PDF_CONFIG.maxImages) {
		return currentImages;
	}
	return [...currentImages, ''];
}

/**
 * Removes an image slot from the form
 */
export function removeImageSlot(currentImages: string[], index: number): string[] {
	if (currentImages.length <= 1) {
		return currentImages;
	}
	return currentImages.filter((_, i) => i !== index);
}

/**
 * Updates an image URL at a specific index
 */
export function updateImageUrl(currentImages: string[], index: number, url: string): string[] {
	const newImages = [...currentImages];
	newImages[index] = url;
	return newImages;
}

/**
 * Generates a placeholder URL for image slots
 */
export function generatePlaceholderUrl(index: number): string {
	return `https://cf2.r2.link/object${index + 1}.png`;
}

/**
 * Formats error messages for display
 */
export function formatErrorMessage(error: unknown): string {
	if (typeof error === 'string') {
		return error;
	}
	if (error && typeof error === 'object' && 'message' in error) {
		return String(error.message);
	}
	return 'An unexpected error occurred';
}

/**
 * Creates a download filename for the PDF
 */
export function createPdfFilename(orderNumber: string): string {
	const timestamp = new Date().toISOString().split('T')[0];
	return `order-${orderNumber}-${timestamp}.pdf`;
}