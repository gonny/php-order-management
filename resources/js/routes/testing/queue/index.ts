import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
import failed from './failed'
/**
* @see \App\Http\Controllers\Testing\QueueTestingController::dashboard
* @see app/Http/Controllers/Testing/QueueTestingController.php:18
* @route '/testing/queue-dashboard'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/testing/queue-dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::dashboard
* @see app/Http/Controllers/Testing/QueueTestingController.php:18
* @route '/testing/queue-dashboard'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::dashboard
* @see app/Http/Controllers/Testing/QueueTestingController.php:18
* @route '/testing/queue-dashboard'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::dashboard
* @see app/Http/Controllers/Testing/QueueTestingController.php:18
* @route '/testing/queue-dashboard'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

const queue = {
    dashboard,
    failed,
}

export default queue