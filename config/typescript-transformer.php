<?php

return [
    'auto_discover_types' => [
        app_path('Data'),
    ],
    'output_file' => resource_path('js/types/generated-dtos.d.ts'),
];

// config/modeltyper.php (keep for Eloquent models)
return [
    'output_path' => resource_path('js/types/generated-models.d.ts'),
];