/**
 * Image Validation Utilities
 * 
 * Utilities for validating image URLs, formats, and properties
 * for PDF generation requirements.
 */

import { PDF_CONFIG } from '../types/pdf';

/**
 * Validates image URL format and accessibility
 */
export async function validateImageUrl(url: string): Promise<{
	isValid: boolean;
	error?: string;
}> {
	// Basic URL validation
	if (!url.trim()) {
		return { isValid: false, error: 'Image URL is required' };
	}

	try {
		new URL(url);
	} catch {
		return { isValid: false, error: 'Invalid URL format' };
	}

	// Check file extension
	const validExtensions = PDF_CONFIG.allowedImageFormats;
	const hasValidExtension = validExtensions.some(ext => 
		url.toLowerCase().includes(`.${ext}`)
	);

	if (!hasValidExtension) {
		return { 
			isValid: false, 
			error: `Image must be ${validExtensions.join(', ').toUpperCase()} format` 
		};
	}

	return { isValid: true };
}

/**
 * Validates multiple image URLs
 */
export async function validateImageUrls(urls: string[]): Promise<{
	isValid: boolean;
	errors: Array<{ index: number; error: string }>;
}> {
	const errors: Array<{ index: number; error: string }> = [];
	
	const validationPromises = urls.map(async (url, index) => {
		const result = await validateImageUrl(url);
		if (!result.isValid && result.error) {
			errors.push({ index, error: result.error });
		}
	});

	await Promise.all(validationPromises);

	return {
		isValid: errors.length === 0,
		errors
	};
}

/**
 * Checks if image URL is from Cloudflare R2
 */
export function isCloudflareR2Url(url: string): boolean {
	try {
		const urlObj = new URL(url);
		return urlObj.hostname.includes('r2.') || 
			   urlObj.hostname.includes('cloudflare') ||
			   urlObj.pathname.includes('r2');
	} catch {
		return false;
	}
}

/**
 * Extracts image filename from URL
 */
export function getImageFilename(url: string): string {
	try {
		const urlObj = new URL(url);
		const pathname = urlObj.pathname;
		return pathname.split('/').pop() || 'image';
	} catch {
		return 'image';
	}
}

/**
 * Validates image dimensions if possible (client-side)
 */
export function validateImageDimensions(url: string): Promise<{
	isValid: boolean;
	dimensions?: { width: number; height: number };
	error?: string;
}> {
	return new Promise((resolve) => {
		const img = new Image();
		
		img.onload = () => {
			const isSquare = img.width === img.height;
			
			resolve({
				isValid: isSquare,
				dimensions: { width: img.width, height: img.height },
				error: !isSquare ? 'Image should be square (600×600px recommended)' : undefined
			});
		};
		
		img.onerror = () => {
			resolve({
				isValid: false,
				error: 'Unable to load image or invalid image format'
			});
		};
		
		// Set a timeout for image loading
		setTimeout(() => {
			resolve({
				isValid: false,
				error: 'Image loading timeout'
			});
		}, 5000);
		
		img.src = url;
	});
}

/**
 * Validates overlay URL format
 */
export function validateOverlayUrl(url: string): {
	isValid: boolean;
	error?: string;
} {
	if (!url.trim()) {
		return { isValid: false, error: 'Overlay URL is required' };
	}

	try {
		new URL(url);
	} catch {
		return { isValid: false, error: 'Invalid overlay URL format' };
	}

	// Check for PNG or SVG format
	const urlLower = url.toLowerCase();
	const isValidFormat = urlLower.includes('.png') || urlLower.includes('.svg');
	
	if (!isValidFormat) {
		return { 
			isValid: false, 
			error: 'Overlay must be PNG or SVG format' 
		};
	}

	return { isValid: true };
}

/**
 * Generates validation summary for all images and overlay
 */
export async function validateAllAssets(
	images: string[], 
	overlayUrl: string
): Promise<{
	isValid: boolean;
	imageErrors: Array<{ index: number; error: string }>;
	overlayError?: string;
}> {
	// Validate images
	const validImages = images.filter(img => img.trim() !== '');
	const imageValidation = await validateImageUrls(validImages);
	
	// Validate overlay
	const overlayValidation = validateOverlayUrl(overlayUrl);
	
	return {
		isValid: imageValidation.isValid && overlayValidation.isValid,
		imageErrors: imageValidation.errors,
		overlayError: overlayValidation.error
	};
}