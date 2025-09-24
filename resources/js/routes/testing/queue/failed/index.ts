import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clear
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
export const clear = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

clear.definition = {
    methods: ["delete"],
    url: '/testing/queue/failed/clear',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clear
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clear
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
clear.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clear.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAll
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
export const retryAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryAll.url(options),
    method: 'post',
})

retryAll.definition = {
    methods: ["post"],
    url: '/testing/queue/failed/retry-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAll
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
retryAll.url = (options?: RouteQueryOptions) => {
    return retryAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAll
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
retryAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryAll.url(options),
    method: 'post',
})

const failed = {
    clear,
    retryAll,
}

export default failed