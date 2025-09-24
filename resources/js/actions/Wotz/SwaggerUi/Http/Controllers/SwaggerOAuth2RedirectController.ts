import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
const SwaggerOAuth2RedirectController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SwaggerOAuth2RedirectController.url(options),
    method: 'get',
})

SwaggerOAuth2RedirectController.definition = {
    methods: ["get","head"],
    url: '/swagger/oauth2-redirect',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
SwaggerOAuth2RedirectController.url = (options?: RouteQueryOptions) => {
    return SwaggerOAuth2RedirectController.definition.url + queryParams(options)
}

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
SwaggerOAuth2RedirectController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: SwaggerOAuth2RedirectController.url(options),
    method: 'get',
})

/**
* @see \Wotz\SwaggerUi\Http\Controllers\SwaggerOAuth2RedirectController::__invoke
* @see vendor/wotz/laravel-swagger-ui/src/Http/Controllers/SwaggerOAuth2RedirectController.php:9
* @route '/swagger/oauth2-redirect'
*/
SwaggerOAuth2RedirectController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: SwaggerOAuth2RedirectController.url(options),
    method: 'head',
})

export default SwaggerOAuth2RedirectController