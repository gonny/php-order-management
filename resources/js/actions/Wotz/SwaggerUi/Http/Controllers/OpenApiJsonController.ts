import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
const OpenApiJsonController = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: OpenApiJsonController.url(args, options),
    method: 'get',
})

OpenApiJsonController.definition = {
    methods: ["get","head"],
    url: '/swagger/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
OpenApiJsonController.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { filename: args }
    }

    if (Array.isArray(args)) {
        args = {
            filename: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        filename: args.filename,
    }

    return OpenApiJsonController.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
OpenApiJsonController.get = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: OpenApiJsonController.url(args, options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
OpenApiJsonController.head = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: OpenApiJsonController.url(args, options),
    method: 'head',
})

export default OpenApiJsonController