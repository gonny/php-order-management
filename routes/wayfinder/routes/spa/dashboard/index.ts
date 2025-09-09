import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\DashboardController::metrics
* @see app/Http/Controllers/Spa/DashboardController.php:15
* @route '/spa/v1/dashboard/metrics'
*/
export const metrics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: metrics.url(options),
    method: 'get',
})

metrics.definition = {
    methods: ["get","head"],
    url: '/spa/v1/dashboard/metrics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\DashboardController::metrics
* @see app/Http/Controllers/Spa/DashboardController.php:15
* @route '/spa/v1/dashboard/metrics'
*/
metrics.url = (options?: RouteQueryOptions) => {
    return metrics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\DashboardController::metrics
* @see app/Http/Controllers/Spa/DashboardController.php:15
* @route '/spa/v1/dashboard/metrics'
*/
metrics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: metrics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\DashboardController::metrics
* @see app/Http/Controllers/Spa/DashboardController.php:15
* @route '/spa/v1/dashboard/metrics'
*/
metrics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: metrics.url(options),
    method: 'head',
})

const dashboard = {
    metrics,
}

export default dashboard