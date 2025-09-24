<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;

class AddressData extends Data
{
    public function __construct(
        #[Required, Enum(['shipping', 'billing'])]
        public string $type = 'shipping',

        #[Required, StringType, Max(255)]
        public string $street1,

        #[Required, StringType, Max(100)]
        public string $city,

        #[Required, StringType, Max(8)]
        public string $postal_code,

        #[Email, Nullable]
        public ?string $email,
    ) {}
}