import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Testing\QueueTestingController::testing
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
export const testing = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: testing.url(options),
    method: 'get',
})

testing.definition = {
    methods: ["get","head"],
    url: '/testing/api-testing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::testing
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
testing.url = (options?: RouteQueryOptions) => {
    return testing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::testing
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
testing.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: testing.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::testing
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
testing.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: testing.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::execute
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
export const execute = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute.url(options),
    method: 'post',
})

execute.definition = {
    methods: ["post"],
    url: '/testing/api-test/execute',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::execute
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
execute.url = (options?: RouteQueryOptions) => {
    return execute.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::execute
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
execute.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute.url(options),
    method: 'post',
})

const api = {
    testing,
    execute,
}

export default api