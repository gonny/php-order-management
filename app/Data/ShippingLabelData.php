<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\In;
use App\Models\Order;

class ShippingLabelData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $tracking_number,
        
        #[Required, In([Order::CARRIER_BALIKOVNA, Order::CARRIER_DPD])]
        public string $carrier,
        
        #[Required, Url]
        public string $label_url,
        
        public ?float $weight,
        
        public ?array $dimensions,
        
        public ?string $service_type,
        
        public ?\DateTime $estimated_delivery,
        
        public array $metadata = [],
    ) {}
}