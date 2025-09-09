import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\QueueWebController::index
* @see app/Http/Controllers/QueueWebController.php:14
* @route '/queues'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/queues',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QueueWebController::index
* @see app/Http/Controllers/QueueWebController.php:14
* @route '/queues'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\QueueWebController::index
* @see app/Http/Controllers/QueueWebController.php:14
* @route '/queues'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\QueueWebController::index
* @see app/Http/Controllers/QueueWebController.php:14
* @route '/queues'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\QueueWebController::failed
* @see app/Http/Controllers/QueueWebController.php:22
* @route '/queues/failed'
*/
export const failed = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failed.url(options),
    method: 'get',
})

failed.definition = {
    methods: ["get","head"],
    url: '/queues/failed',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\QueueWebController::failed
* @see app/Http/Controllers/QueueWebController.php:22
* @route '/queues/failed'
*/
failed.url = (options?: RouteQueryOptions) => {
    return failed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\QueueWebController::failed
* @see app/Http/Controllers/QueueWebController.php:22
* @route '/queues/failed'
*/
failed.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failed.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\QueueWebController::failed
* @see app/Http/Controllers/QueueWebController.php:22
* @route '/queues/failed'
*/
failed.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: failed.url(options),
    method: 'head',
})

const queues = {
    index,
    failed,
}

export default queues