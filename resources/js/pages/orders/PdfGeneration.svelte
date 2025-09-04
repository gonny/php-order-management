<script lang="ts">
	import { router } from '@inertiajs/svelte';
	import AppLayout from '@/layouts/AppLayout.svelte';
	import * as Card from '@/components/ui/card';
	import { 
		PdfGenerationForm, 
		PdfPreview, 
		PDF_SPECS 
	} from '@/packages/pdf-generator';

	interface Props {
		order: {
			id: string;
			number: string;
			status: string;
			total_amount: number;
			currency: string;
			client?: {
				name: string;
				email: string;
			};
			items?: Array<{
				id: string;
				name: string;
				quantity: number;
				price: number;
			}>;
		};
		pdfExists: boolean;
	}

	let { order, pdfExists = false }: Props = $props();

	/**
	 * Handles successful PDF generation
	 */
	function handlePdfSuccess(): void {
		// Reload page to update PDF status
		setTimeout(() => {
			router.reload();
		}, 1000);
	}

	/**
	 * Handles form cancellation
	 */
	function handleCancel(): void {
		router.visit('/dashboard');
	}
</script>

<svelte:head>
	<title>PDF Generation - Order {order.number}</title>
</svelte:head>

<AppLayout>
	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
				<!-- PDF Preview and Order Information -->
				<PdfPreview
					orderId={order.id}
					orderNumber={order.number}
					{pdfExists}
					clientName={order.client?.name}
					totalAmount={order.total_amount}
					currency={order.currency}
					status={order.status}
					itemCount={order.items?.length}
				/>

				<!-- PDF Generation Form -->
				<Card.Root>
					<Card.Header>
						<Card.Title>Generate New PDF</Card.Title>
						<Card.Description>
							Create a 3×3 grid PDF with images from Cloudflare R2 and overlay.
						</Card.Description>
					</Card.Header>
					<Card.Content>
						<PdfGenerationForm
							orderId={order.id}
							onCancel={handleCancel}
							onSuccess={handlePdfSuccess}
						/>
					</Card.Content>
				</Card.Root>

				<!-- Technical Specifications -->
				<Card.Root class="mt-6">
					<Card.Header>
						<Card.Title>PDF Specifications</Card.Title>
					</Card.Header>
					<Card.Content class="text-sm text-gray-600">
						<ul class="space-y-1">
							<li>• Format: {PDF_SPECS.format}</li>
							<li>• Layout: {PDF_SPECS.layout.grid} of {PDF_SPECS.layout.cells}</li>
							<li>• Images: {PDF_SPECS.imageRequirements.size}, resized to fit cell size</li>
							<li>• Overlay: {PDF_SPECS.overlayFormats.join('/')} with transparency</li>
							<li>• Margins: {PDF_SPECS.margins}</li>
						</ul>
					</Card.Content>
				</Card.Root>
			</div>
		</div>
	</div>
</AppLayout>