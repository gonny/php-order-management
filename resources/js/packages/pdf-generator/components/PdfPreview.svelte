<!--
	PDF Preview Component
	
	Component for displaying PDF download status and providing
	download functionality for generated PDFs.
-->
<script lang="ts">
	import Button from '@/components/ui/button/button.svelte';
	import * as Card from '@/components/ui/card';
	import { usePdfGeneration } from '../hooks/use-pdf-generation';
	import { createPdfFilename } from '../utils/pdf-helpers';

	interface Props {
		orderId: string;
		orderNumber: string;
		pdfExists: boolean;
		clientName?: string;
		totalAmount?: number;
		currency?: string;
		status?: string;
		itemCount?: number;
	}

	let { 
		orderId, 
		orderNumber, 
		pdfExists, 
		clientName,
		totalAmount,
		currency,
		status,
		itemCount
	}: Props = $props();

	const { downloadPdf } = usePdfGeneration();

	/**
	 * Handles PDF download
	 */
	function handleDownload(): void {
		downloadPdf(orderId);
	}

	/**
	 * Formats currency display
	 */
	function formatCurrency(amount?: number, curr?: string): string {
		if (amount === undefined || !curr) return '';
		return `${curr} ${amount.toFixed(2)}`;
	}

	/**
	 * Gets the generated filename for display
	 */
	function getExpectedFilename(): string {
		return createPdfFilename(orderNumber);
	}
</script>

<!-- Order Information Card -->
<Card.Root class="mb-6">
	<Card.Header>
		<Card.Title>Order #{orderNumber}</Card.Title>
		<Card.Description class="space-y-1">
			{#if status}
				<div>Status: <span class="font-medium">{status}</span></div>
			{/if}
			{#if totalAmount !== undefined && currency}
				<div>Total: <span class="font-medium">{formatCurrency(totalAmount, currency)}</span></div>
			{/if}
		</Card.Description>
	</Card.Header>
	<Card.Content class="space-y-2">
		{#if clientName}
			<p><strong>Client:</strong> {clientName}</p>
		{/if}
		{#if itemCount !== undefined}
			<p><strong>Items:</strong> {itemCount} item(s)</p>
		{/if}
	</Card.Content>
</Card.Root>

<!-- PDF Status Card -->
{#if pdfExists}
	<Card.Root class="mb-6 bg-green-50 border-green-200">
		<Card.Header>
			<div class="flex items-center space-x-2">
				<svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
				</svg>
				<Card.Title class="text-green-800">PDF Ready</Card.Title>
			</div>
			<Card.Description class="text-green-600">
				A PDF has been generated for this order and is ready for download.
			</Card.Description>
		</Card.Header>
		<Card.Content class="space-y-4">
			<!-- Download Button -->
			<div class="flex items-center space-x-4">
				<Button 
					onclick={handleDownload} 
					class="bg-green-600 hover:bg-green-700 text-white"
				>
					<svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
					</svg>
					Download PDF
				</Button>
			</div>

			<!-- File Information -->
			<div class="text-sm text-green-700 bg-green-100 p-3 rounded-md">
				<p><strong>Filename:</strong> {getExpectedFilename()}</p>
				<p><strong>Format:</strong> PDF Document</p>
			</div>
		</Card.Content>
	</Card.Root>
{:else}
	<Card.Root class="mb-6 bg-orange-50 border-orange-200">
		<Card.Header>
			<div class="flex items-center space-x-2">
				<svg class="h-5 w-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
				</svg>
				<Card.Title class="text-orange-800">No PDF Available</Card.Title>
			</div>
			<Card.Description class="text-orange-600">
				No PDF has been generated for this order yet. Use the form below to create one.
			</Card.Description>
		</Card.Header>
	</Card.Root>
{/if}

<!-- PDF Generation Instructions -->
{#if !pdfExists}
	<Card.Root class="mb-6 bg-blue-50 border-blue-200">
		<Card.Header>
			<Card.Title class="text-blue-800">PDF Generation</Card.Title>
			<Card.Description class="text-blue-600">
				Generate a custom 3×3 grid PDF with your images and overlay
			</Card.Description>
		</Card.Header>
		<Card.Content class="text-sm text-blue-700">
			<div class="space-y-2">
				<p><strong>Requirements:</strong></p>
				<ul class="list-disc list-inside space-y-1 ml-4">
					<li>1-9 image URLs (preferably 600×600px)</li>
					<li>Images should be hosted on Cloudflare R2</li>
					<li>Overlay URL (PNG or SVG format)</li>
					<li>Cell size between 100-600 pixels</li>
				</ul>
			</div>
		</Card.Content>
	</Card.Root>
{/if}