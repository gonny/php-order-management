import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/swagger',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
export const oauth2Redirect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: oauth2Redirect.url(options),
    method: 'get',
})

oauth2Redirect.definition = {
    methods: ["get","head"],
    url: '/swagger/oauth2-redirect',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
oauth2Redirect.url = (options?: RouteQueryOptions) => {
    return oauth2Redirect.definition.url + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
oauth2Redirect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: oauth2Redirect.url(options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
oauth2Redirect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: oauth2Redirect.url(options),
    method: 'head',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
export const json = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: json.url(args, options),
    method: 'get',
})

json.definition = {
    methods: ["get","head"],
    url: '/swagger/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
json.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return json.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
json.get = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: json.url(args, options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\OpenApiJsonController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/OpenApiJsonController.php:14
* @route '/swagger/{filename}'
*/
json.head = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: json.url(args, options),
    method: 'head',
})

const swagger = {
    index,
    oauth2Redirect,
    json,
}

export default swagger