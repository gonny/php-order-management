<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;

class OrderItemData extends Data
{
    public function __construct(
        public ?int $id,
        
        #[Required, IntegerType]
        public int $product_id,
        
        #[Required, StringType]
        public string $product_name,
        
        public ?string $product_sku,
        
        #[Required, IntegerType, Min(1)]
        public int $quantity,
        
        #[Required, Numeric, Min(0)]
        public float $price,
        
        #[Required, Numeric, Min(0)]
        public float $subtotal,
        
        public array $metadata = [],
    ) {}
    
    public static function rules(): array
    {
        return [
            'subtotal' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $data = request()->input(str_replace('.subtotal', '', $attribute));
                    $calculated = $data['quantity'] * $data['price'];
                    if (abs($value - $calculated) > 0.01) {
                        $fail('Subtotal must equal quantity × price.');
                    }
                },
            ],
        ];
    }
}