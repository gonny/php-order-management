import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
const SwaggerViewController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SwaggerViewController.url(options),
    method: 'get',
})

SwaggerViewController.definition = {
    methods: ["get","head"],
    url: '/swagger',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
SwaggerViewController.url = (options?: RouteQueryOptions) => {
    return SwaggerViewController.definition.url + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
SwaggerViewController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SwaggerViewController.url(options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerViewController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerViewController.php:11
* @route '/swagger'
*/
SwaggerViewController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: SwaggerViewController.url(options),
    method: 'head',
})

export default SwaggerViewController