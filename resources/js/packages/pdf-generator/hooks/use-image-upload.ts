/**
 * Image Upload Management Hook
 * 
 * Provides functionality for managing image URLs in the PDF generation form,
 * including validation, slot management, and URL handling.
 */

import type { ImageSlot } from '../types/pdf';
import { 
	addImageSlot, 
	removeImageSlot, 
	updateImageUrl, 
	createImageSlots,
	generatePlaceholderUrl 
} from '../utils/pdf-helpers';
import { validateImageUrl } from '../utils/image-validation';

export interface UseImageUploadReturn {
	imageSlots: ImageSlot[];
	addSlot: () => void;
	removeSlot: (index: number) => void;
	updateSlot: (index: number, url: string) => void;
	validateSlot: (index: number) => Promise<void>;
	getImageUrls: () => string[];
	canAddSlot: boolean;
	canRemoveSlot: boolean;
}

/**
 * Hook for managing image URL slots in PDF generation
 */
export function useImageUpload(initialImages: string[] = ['']): UseImageUploadReturn {
	let imageUrls = $state([...initialImages]);
	let imageSlots = $state(createImageSlots(imageUrls));

	// Update slots when URLs change
	$effect(() => {
		imageSlots = createImageSlots(imageUrls);
	});

	/**
	 * Adds a new empty image slot
	 */
	function addSlot(): void {
		const newUrls = addImageSlot(imageUrls);
		if (newUrls.length > imageUrls.length) {
			imageUrls = newUrls;
		}
	}

	/**
	 * Removes an image slot at the specified index
	 */
	function removeSlot(index: number): void {
		const newUrls = removeImageSlot(imageUrls, index);
		if (newUrls.length !== imageUrls.length) {
			imageUrls = newUrls;
		}
	}

	/**
	 * Updates the URL at the specified index
	 */
	function updateSlot(index: number, url: string): void {
		const newUrls = updateImageUrl(imageUrls, index, url);
		imageUrls = newUrls;
	}

	/**
	 * Validates an image slot and updates its validity
	 */
	async function validateSlot(index: number): Promise<void> {
		const url = imageUrls[index];
		if (!url || url.trim() === '') {
			// Empty slots are valid (will be filtered out)
			return;
		}

		try {
			const validation = await validateImageUrl(url);
			// Update the slot validity
			imageSlots[index] = {
				...imageSlots[index],
				isValid: validation.isValid
			};
		} catch (error) {
			console.error('Image validation error:', error);
			imageSlots[index] = {
				...imageSlots[index],
				isValid: false
			};
		}
	}

	/**
	 * Gets all image URLs (filtered for valid ones)
	 */
	function getImageUrls(): string[] {
		return imageUrls.filter(url => url.trim() !== '');
	}

	return {
		get imageSlots() {
			return imageSlots;
		},
		addSlot,
		removeSlot,
		updateSlot,
		validateSlot,
		getImageUrls,
		get canAddSlot() {
			return imageUrls.length < 9;
		},
		get canRemoveSlot() {
			return imageUrls.length > 1;
		}
	};
}

/**
 * Hook for managing image placeholder generation
 */
export function useImagePlaceholders() {
	/**
	 * Generates placeholder URLs for all slots
	 */
	function generatePlaceholders(count: number = 9): string[] {
		return Array.from({ length: count }, (_, index) => 
			generatePlaceholderUrl(index)
		);
	}

	/**
	 * Fills empty slots with placeholder URLs
	 */
	function fillWithPlaceholders(currentUrls: string[]): string[] {
		return currentUrls.map((url, index) => 
			url.trim() === '' ? generatePlaceholderUrl(index) : url
		);
	}

	/**
	 * Clears all placeholder URLs
	 */
	function clearPlaceholders(currentUrls: string[]): string[] {
		return currentUrls.map(url => 
			url.includes('cf2.r2.link/object') ? '' : url
		);
	}

	return {
		generatePlaceholders,
		fillWithPlaceholders,
		clearPlaceholders
	};
}

/**
 * Hook for batch image operations
 */
export function useBatchImageOperations() {
	/**
	 * Validates all image URLs in batch
	 */
	async function validateAllImages(urls: string[]): Promise<{
		validCount: number;
		invalidCount: number;
		errors: Array<{ index: number; error: string }>;
	}> {
		const errors: Array<{ index: number; error: string }> = [];
		let validCount = 0;
		let invalidCount = 0;

		const validationPromises = urls.map(async (url, index) => {
			if (url.trim() === '') {
				return; // Skip empty URLs
			}

			try {
				const validation = await validateImageUrl(url);
				if (validation.isValid) {
					validCount++;
				} else {
					invalidCount++;
					if (validation.error) {
						errors.push({ index, error: validation.error });
					}
				}
			} catch (error) {
				invalidCount++;
				errors.push({ 
					index, 
					error: error instanceof Error ? error.message : 'Validation failed' 
				});
			}
		});

		await Promise.all(validationPromises);

		return {
			validCount,
			invalidCount,
			errors
		};
	}

	/**
	 * Clears all image URLs
	 */
	function clearAllImages(): string[] {
		return [''];
	}

	/**
	 * Resets to default image count
	 */
	function resetToDefault(count: number = 3): string[] {
		return Array.from({ length: count }, () => '');
	}

	return {
		validateAllImages,
		clearAllImages,
		resetToDefault
	};
}