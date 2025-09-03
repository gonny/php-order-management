/**
 * PDF Generator Package Types
 * 
 * Type definitions for PDF generation functionality including
 * form data, API requests/responses, and validation interfaces.
 */

export interface PdfGenerationForm {
	images: string[];
	cell_size: number;
	overlay_url: string;
}

export interface PdfGenerationRequest {
	images: string[];
	cell_size: number;
	overlay_url: string;
}

export interface PdfGenerationResponse {
	message: string;
	pdf_url?: string;
	status: 'processing' | 'completed' | 'failed';
}

export interface PdfGenerationError {
	message: string;
	errors?: Record<string, string[]>;
}

export interface ImageSlot {
	id: number;
	url: string;
	isValid: boolean;
}

export interface PdfConfiguration {
	maxImages: number;
	minImages: number;
	minCellSize: number;
	maxCellSize: number;
	defaultCellSize: number;
	allowedImageFormats: string[];
	maxImageSize: number;
}

export interface PdfSpecifications {
	format: string;
	dimensions: {
		width: string;
		height: string;
	};
	dpi: number;
	layout: {
		grid: string;
		cells: string;
	};
	imageRequirements: {
		size: string;
		formats: string[];
	};
	overlayFormats: string[];
	margins: string;
}

export const PDF_CONFIG: PdfConfiguration = {
	maxImages: 9,
	minImages: 1,
	minCellSize: 100,
	maxCellSize: 600,
	defaultCellSize: 200,
	allowedImageFormats: ['png', 'jpg', 'jpeg'],
	maxImageSize: 10 * 1024 * 1024, // 10MB
};

export const PDF_SPECS: PdfSpecifications = {
	format: 'Letter (8.5" × 11") at 300 DPI',
	dimensions: {
		width: '8.5"',
		height: '11"'
	},
	dpi: 300,
	layout: {
		grid: '3×3 grid',
		cells: 'square cells'
	},
	imageRequirements: {
		size: '600×600px',
		formats: ['PNG', 'JPG', 'JPEG']
	},
	overlayFormats: ['PNG', 'SVG'],
	margins: 'Top/bottom only, no left/right margins'
};