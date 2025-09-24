<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class OrderData extends Data
{
    public function __construct(
        public ?int $id,
        
        #[Required, StringType]
        public string $order_number,
        
        #[Required, Exists('customers', 'id')]
        public int $customer_id,
        
        public ?CustomerData $customer,
        
        #[Required, In(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])]
        public string $status,
        
        #[Required, Numeric, Min(0)]
        public float $subtotal,
        
        #[Required, Numeric, Min(0)]
        public float $tax_amount,
        
        #[Required, Numeric, Min(0)]
        public float $shipping_amount,
        
        #[Required, Numeric, Min(0)]
        public float $total_amount,
        
        #[Required, DataCollectionOf(OrderItemData::class)]
        public DataCollection $items,
        
        #[Required, DataCollectionOf(AddressData::class)]
        public DataCollection $addresses,
        
        public ?ShippingLabelData $shipping_label,
        
        public ?string $cloudflare_pdf_url,
        
        public ?string $invoice_pdf_url,
        
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?\DateTime $shipped_at,
        
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?\DateTime $delivered_at,
        
        public array $metadata = [],
        
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?\DateTime $created_at = null,
        
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?\DateTime $updated_at = null,
    ) {}
    
    public static function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'addresses' => ['required', 'array', 'min:1', 'max:3'],
            'total_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $data = request()->all();
                    $calculated = $data['subtotal'] + $data['tax_amount'] + $data['shipping_amount'];
                    if (abs($value - $calculated) > 0.01) {
                        $fail('Total amount must equal subtotal + tax + shipping.');
                    }
                },
            ],
        ];
    }
    
    public static function fromModel($order): self
    {
        return new self(
            id: $order->id,
            order_number: $order->order_number,
            customer_id: $order->customer_id,
            customer: $order->relationLoaded('customer') 
                ? CustomerData::fromModel($order->customer)
                : null,
            status: $order->status,
            subtotal: $order->subtotal,
            tax_amount: $order->tax_amount,
            shipping_amount: $order->shipping_amount,
            total_amount: $order->total_amount,
            items: OrderItemData::collect($order->items),
            addresses: AddressData::collect($order->addresses),
            shipping_label: $order->shipping_label 
                ? ShippingLabelData::from($order->shipping_label)
                : null,
            cloudflare_pdf_url: $order->cloudflare_pdf_url,
            invoice_pdf_url: $order->invoice_pdf_url,
            shipped_at: $order->shipped_at,
            delivered_at: $order->delivered_at,
            metadata: $order->metadata ?? [],
            created_at: $order->created_at,
            updated_at: $order->updated_at,
        );
    }
}