<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class CustomerData extends Data
{
    public function __construct(
        public ?int $id,
        
        #[Required, StringType]
        public string $name,
        
        #[Required, Email]
        public string $email,
        
        public ?string $phone,
        
        #[DataCollectionOf(AddressData::class)]
        public ?DataCollection $addresses,
        
        public array $metadata = [],
    ) {}
    
    public static function fromModel($customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name,
            email: $customer->email,
            phone: $customer->phone,
            addresses: $customer->addresses
                ? AddressData::collect($customer->addresses)
                : null,
            metadata: $customer->metadata ?? [],
        );
    }
}