import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\QueueController::stats
* @see app/Http/Controllers/Api/V1/QueueController.php:22
* @route '/api/v1/queues/stats'
*/
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/api/v1/queues/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::stats
* @see app/Http/Controllers/Api/V1/QueueController.php:22
* @route '/api/v1/queues/stats'
*/
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::stats
* @see app/Http/Controllers/Api/V1/QueueController.php:22
* @route '/api/v1/queues/stats'
*/
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::stats
* @see app/Http/Controllers/Api/V1/QueueController.php:22
* @route '/api/v1/queues/stats'
*/
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::failedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:50
* @route '/api/v1/queues/failed'
*/
export const failedJobs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failedJobs.url(options),
    method: 'get',
})

failedJobs.definition = {
    methods: ["get","head"],
    url: '/api/v1/queues/failed',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::failedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:50
* @route '/api/v1/queues/failed'
*/
failedJobs.url = (options?: RouteQueryOptions) => {
    return failedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::failedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:50
* @route '/api/v1/queues/failed'
*/
failedJobs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failedJobs.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::failedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:50
* @route '/api/v1/queues/failed'
*/
failedJobs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: failedJobs.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::recentJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:177
* @route '/api/v1/queues/recent'
*/
export const recentJobs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentJobs.url(options),
    method: 'get',
})

recentJobs.definition = {
    methods: ["get","head"],
    url: '/api/v1/queues/recent',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::recentJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:177
* @route '/api/v1/queues/recent'
*/
recentJobs.url = (options?: RouteQueryOptions) => {
    return recentJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::recentJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:177
* @route '/api/v1/queues/recent'
*/
recentJobs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentJobs.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::recentJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:177
* @route '/api/v1/queues/recent'
*/
recentJobs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recentJobs.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::retryJob
* @see app/Http/Controllers/Api/V1/QueueController.php:96
* @route '/api/v1/queues/failed/{jobId}/retry'
*/
export const retryJob = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryJob.url(args, options),
    method: 'post',
})

retryJob.definition = {
    methods: ["post"],
    url: '/api/v1/queues/failed/{jobId}/retry',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::retryJob
* @see app/Http/Controllers/Api/V1/QueueController.php:96
* @route '/api/v1/queues/failed/{jobId}/retry'
*/
retryJob.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jobId: args }
    }

    if (Array.isArray(args)) {
        args = {
            jobId: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        jobId: args.jobId,
    }

    return retryJob.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::retryJob
* @see app/Http/Controllers/Api/V1/QueueController.php:96
* @route '/api/v1/queues/failed/{jobId}/retry'
*/
retryJob.post = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryJob.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::deleteFailedJob
* @see app/Http/Controllers/Api/V1/QueueController.php:123
* @route '/api/v1/queues/failed/{jobId}'
*/
export const deleteFailedJob = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFailedJob.url(args, options),
    method: 'delete',
})

deleteFailedJob.definition = {
    methods: ["delete"],
    url: '/api/v1/queues/failed/{jobId}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::deleteFailedJob
* @see app/Http/Controllers/Api/V1/QueueController.php:123
* @route '/api/v1/queues/failed/{jobId}'
*/
deleteFailedJob.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { jobId: args }
    }

    if (Array.isArray(args)) {
        args = {
            jobId: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        jobId: args.jobId,
    }

    return deleteFailedJob.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::deleteFailedJob
* @see app/Http/Controllers/Api/V1/QueueController.php:123
* @route '/api/v1/queues/failed/{jobId}'
*/
deleteFailedJob.delete = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFailedJob.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\V1\QueueController::clearFailedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:150
* @route '/api/v1/queues/failed'
*/
export const clearFailedJobs = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearFailedJobs.url(options),
    method: 'delete',
})

clearFailedJobs.definition = {
    methods: ["delete"],
    url: '/api/v1/queues/failed',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\QueueController::clearFailedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:150
* @route '/api/v1/queues/failed'
*/
clearFailedJobs.url = (options?: RouteQueryOptions) => {
    return clearFailedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\QueueController::clearFailedJobs
* @see app/Http/Controllers/Api/V1/QueueController.php:150
* @route '/api/v1/queues/failed'
*/
clearFailedJobs.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: clearFailedJobs.url(options),
    method: 'delete',
})

const QueueController = { stats, failedJobs, recentJobs, retryJob, deleteFailedJob, clearFailedJobs }

export default QueueController