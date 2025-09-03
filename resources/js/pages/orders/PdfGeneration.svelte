<script lang="ts">
	import { router, useForm } from '@inertiajs/svelte';
	import { toast } from 'svelte-sonner';
	import Button from '@/components/ui/button/button.svelte';
	import * as Card from '@/components/ui/card';
	import Input from '@/components/ui/input/input.svelte';
	import Label from '@/components/ui/label/label.svelte';
	import AppLayout from '@/layouts/AppLayout.svelte';

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

	// Form for PDF generation
	const form = useForm({
		images: ['', '', '', '', '', '', '', '', ''], // 9 slots for images
		cell_size: 200,
		overlay_url: ''
	});

	function addImageSlot() {
		if ($form.images.length < 9) {
			$form.images = [...$form.images, ''];
		}
	}

	function removeImageSlot(index: number) {
		if ($form.images.length > 1) {
			$form.images = $form.images.filter((_, i) => i !== index);
		}
	}

	function updateImageUrl(index: number, url: string) {
		$form.images[index] = url;
	}

	async function generatePdf() {
		// Filter out empty image URLs
		const validImages = $form.images.filter(img => img.trim() !== '');
		
		if (validImages.length === 0) {
			toast.error('Please add at least one image URL');
			return;
		}

		if (!$form.overlay_url.trim()) {
			toast.error('Please provide an overlay URL');
			return;
		}

		// Create the API request with HMAC authentication
		try {
			const response = await fetch(`/api/v1/orders/${order.id}/pdf`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					// In a real implementation, you'd add HMAC headers here
					// For now, we'll simulate the API call
				},
				body: JSON.stringify({
					images: validImages,
					cell_size: $form.cell_size,
					overlay_url: $form.overlay_url
				})
			});

			if (response.ok) {
				await response.json();
				toast.success('PDF generation started! Check back in a few moments.');
				// Optionally refresh the page or update state
				setTimeout(() => {
					router.reload();
				}, 2000);
			} else {
				const error = await response.json();
				toast.error(error.message || 'Failed to generate PDF');
			}
		} catch (error) {
			console.error('PDF generation error:', error);
			toast.error('Failed to generate PDF. Please try again.');
		}
	}

	function downloadPdf() {
		if (pdfExists) {
			window.location.href = `/orders/${order.id}/pdf`;
		}
	}
</script>

<svelte:head>
	<title>PDF Generation - Order {order.number}</title>
</svelte:head>

<AppLayout>
	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
				<!-- Order Information -->
				<Card.Root class="mb-6">
					<Card.Header>
						<Card.Title>Order #{order.number}</Card.Title>
						<Card.Description>
							Status: {order.status} | Total: {order.currency} {order.total_amount}
						</Card.Description>
					</Card.Header>
					<Card.Content>
						{#if order.client}
							<p><strong>Client:</strong> {order.client.name} ({order.client.email})</p>
						{/if}
						{#if order.items}
							<p><strong>Items:</strong> {order.items.length} item(s)</p>
						{/if}
					</Card.Content>
				</Card.Root>

				<!-- PDF Status -->
				{#if pdfExists}
					<Card.Root class="mb-6 bg-green-50 border-green-200">
						<Card.Header>
							<Card.Title class="text-green-800">PDF Ready</Card.Title>
							<Card.Description class="text-green-600">
								A PDF has been generated for this order.
							</Card.Description>
						</Card.Header>
						<Card.Content>
							<Button onclick={downloadPdf} class="bg-green-600 hover:bg-green-700">
								Download PDF
							</Button>
						</Card.Content>
					</Card.Root>
				{/if}

				<!-- PDF Generation Form -->
				<Card.Root>
					<Card.Header>
						<Card.Title>Generate New PDF</Card.Title>
						<Card.Description>
							Create a 3×3 grid PDF with images from Cloudflare R2 and overlay.
						</Card.Description>
					</Card.Header>
					<Card.Content class="space-y-6">
						<form onsubmit={(e) => { e.preventDefault(); generatePdf(); }}>
							<!-- Cell Size -->
							<div class="space-y-2">
								<Label for="cell_size">Cell Size (pixels)</Label>
								<Input
									id="cell_size"
									type="number"
									min="100"
									max="600"
									bind:value={$form.cell_size}
									placeholder="200"
									required
								/>
								<p class="text-sm text-gray-500">Size of each grid cell (100-600px)</p>
							</div>

							<!-- Overlay URL -->
							<div class="space-y-2">
								<Label for="overlay_url">Overlay URL</Label>
								<Input
									id="overlay_url"
									type="url"
									bind:value={$form.overlay_url}
									placeholder="https://cdn.domain.com/overlays/cropmark.svg"
									required
								/>
								<p class="text-sm text-gray-500">PNG/SVG overlay to place over each image</p>
							</div>

							<!-- Image URLs -->
							<div class="space-y-4">
								<div class="flex items-center justify-between">
									<Label>Image URLs (1-9 images)</Label>
									{#if $form.images.length < 9}
										<Button type="button" variant="outline" size="sm" onclick={addImageSlot}>
											Add Image
										</Button>
									{/if}
								</div>

								{#each $form.images as imageUrl, index (index)}
									<div class="flex space-x-2">
										<div class="flex-1">
											<Input
												type="url"
												value={imageUrl}
												onchange={(e) => {
													const target = e.target as HTMLInputElement;
													if (target) {
														updateImageUrl(index, target.value);
													}
												}}
												placeholder="https://cf2.r2.link/object{index + 1}.png"
												class="w-full"
											/>
										</div>
										{#if $form.images.length > 1}
											<Button
												type="button"
												variant="outline"
												size="sm"
												onclick={() => removeImageSlot(index)}
											>
												Remove
											</Button>
										{/if}
									</div>
								{/each}

								<p class="text-sm text-gray-500">
									Images should be 600×600px from Cloudflare R2. Empty slots will be left blank in the grid.
								</p>
							</div>

							<!-- Submit Button -->
							<div class="flex space-x-4">
								<Button
									type="submit"
									disabled={$form.processing}
									class="bg-blue-600 hover:bg-blue-700"
								>
									{#if $form.processing}
										Generating PDF...
									{:else}
										Generate PDF
									{/if}
								</Button>

								<Button
									type="button"
									variant="outline"
									onclick={() => router.visit('/dashboard')}
								>
									Cancel
								</Button>
							</div>
						</form>
					</Card.Content>
				</Card.Root>

				<!-- Technical Specifications -->
				<Card.Root class="mt-6">
					<Card.Header>
						<Card.Title>PDF Specifications</Card.Title>
					</Card.Header>
					<Card.Content class="text-sm text-gray-600">
						<ul class="space-y-1">
							<li>• Format: Letter (8.5" × 11") at 300 DPI</li>
							<li>• Layout: 3×3 grid of square cells</li>
							<li>• Images: 600×600px, resized to fit cell size</li>
							<li>• Overlay: PNG/SVG with transparency</li>
							<li>• Margins: Top/bottom only, no left/right margins</li>
						</ul>
					</Card.Content>
				</Card.Root>
			</div>
		</div>
	</div>
</AppLayout>