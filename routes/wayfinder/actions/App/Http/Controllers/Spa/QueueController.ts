import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\QueueController::stats
* @see app/Http/Controllers/Spa/QueueController.php:22
* @route '/spa/v1/queues/stats'
*/
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/spa/v1/queues/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::stats
* @see app/Http/Controllers/Spa/QueueController.php:22
* @route '/spa/v1/queues/stats'
*/
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::stats
* @see app/Http/Controllers/Spa/QueueController.php:22
* @route '/spa/v1/queues/stats'
*/
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::stats
* @see app/Http/Controllers/Spa/QueueController.php:22
* @route '/spa/v1/queues/stats'
*/
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::failedJobs
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
export const failedJobs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failedJobs.url(options),
    method: 'get',
})

failedJobs.definition = {
    methods: ["get","head"],
    url: '/spa/v1/queues/failed',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::failedJobs
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failedJobs.url = (options?: RouteQueryOptions) => {
    return failedJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::failedJobs
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failedJobs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failedJobs.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::failedJobs
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failedJobs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: failedJobs.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::recentJobs
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
export const recentJobs = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentJobs.url(options),
    method: 'get',
})

recentJobs.definition = {
    methods: ["get","head"],
    url: '/spa/v1/queues/recent',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::recentJobs
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recentJobs.url = (options?: RouteQueryOptions) => {
    return recentJobs.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::recentJobs
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recentJobs.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recentJobs.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::recentJobs
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recentJobs.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recentJobs.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::retryJob
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
*/
export const retryJob = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryJob.url(args, options),
    method: 'post',
})

retryJob.definition = {
    methods: ["post"],
    url: '/spa/v1/queues/failed/{jobId}/retry',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::retryJob
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
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
* @see \App\Http\Controllers\Spa\QueueController::retryJob
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
*/
retryJob.post = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retryJob.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::deleteFailedJob
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
*/
export const deleteFailedJob = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFailedJob.url(args, options),
    method: 'delete',
})

deleteFailedJob.definition = {
    methods: ["delete"],
    url: '/spa/v1/queues/failed/{jobId}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::deleteFailedJob
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
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
* @see \App\Http\Controllers\Spa\QueueController::deleteFailedJob
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
*/
deleteFailedJob.delete = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFailedJob.url(args, options),
    method: 'delete',
})

const QueueController = { stats, failedJobs, recentJobs, retryJob, deleteFailedJob }

export default QueueController