import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../../wayfinder'
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

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::apiTesting
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
export const apiTesting = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: apiTesting.url(options),
    method: 'get',
})

apiTesting.definition = {
    methods: ["get","head"],
    url: '/testing/api-testing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::apiTesting
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
apiTesting.url = (options?: RouteQueryOptions) => {
    return apiTesting.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::apiTesting
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
apiTesting.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: apiTesting.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::apiTesting
* @see app/Http/Controllers/Testing/QueueTestingController.php:120
* @route '/testing/api-testing'
*/
apiTesting.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: apiTesting.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::executeTest
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
export const executeTest = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: executeTest.url(options),
    method: 'post',
})

executeTest.definition = {
    methods: ["post"],
    url: '/testing/api-test/execute',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::executeTest
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
executeTest.url = (options?: RouteQueryOptions) => {
    return executeTest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::executeTest
* @see app/Http/Controllers/Testing/QueueTestingController.php:278
* @route '/testing/api-test/execute'
*/
executeTest.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: executeTest.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clearFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
export const clearFailedJobs = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearFailedJobs.url(options),
    method: 'delete',
})

clearFailedJobs.definition = {
    methods: ["delete"],
    url: '/testing/queue/failed/clear',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clearFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
clearFailedJobs.url = (options?: RouteQueryOptions) => {
    return clearFailedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::clearFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:363
* @route '/testing/queue/failed/clear'
*/
clearFailedJobs.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearFailedJobs.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAllFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
export const retryAllFailedJobs = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryAllFailedJobs.url(options),
    method: 'post',
})

retryAllFailedJobs.definition = {
    methods: ["post"],
    url: '/testing/queue/failed/retry-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAllFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
retryAllFailedJobs.url = (options?: RouteQueryOptions) => {
    return retryAllFailedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Testing\QueueTestingController::retryAllFailedJobs
* @see app/Http/Controllers/Testing/QueueTestingController.php:376
* @route '/testing/queue/failed/retry-all'
*/
retryAllFailedJobs.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryAllFailedJobs.url(options),
    method: 'post',
})

const QueueTestingController = { dashboard, apiTesting, executeTest, clearFailedJobs, retryAllFailedJobs }

export default QueueTestingController