<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Nullable;

class AddressData extends Data
{
    public function __construct(
        #[Required, Enum(['shipping', 'billing'])]
        public string $type,

        #[Required]
        public string $street1,

        #[Required]
        public string $city,

        #[Required]
        public string $postal_code,

        #[Email, Nullable]
        public ?string $email,
    ) {}
}