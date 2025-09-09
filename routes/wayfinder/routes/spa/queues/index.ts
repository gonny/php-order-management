import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
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
* @see \App\Http\Controllers\Spa\QueueController::failed
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
export const failed = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failed.url(options),
    method: 'get',
})

failed.definition = {
    methods: ["get","head"],
    url: '/spa/v1/queues/failed',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::failed
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failed.url = (options?: RouteQueryOptions) => {
    return failed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::failed
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failed.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: failed.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::failed
* @see app/Http/Controllers/Spa/QueueController.php:49
* @route '/spa/v1/queues/failed'
*/
failed.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: failed.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::recent
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
export const recent = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})

recent.definition = {
    methods: ["get","head"],
    url: '/spa/v1/queues/recent',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::recent
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recent.url = (options?: RouteQueryOptions) => {
    return recent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::recent
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recent.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: recent.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::recent
* @see app/Http/Controllers/Spa/QueueController.php:90
* @route '/spa/v1/queues/recent'
*/
recent.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: recent.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::retry
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
*/
export const retry = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retry.url(args, options),
    method: 'post',
})

retry.definition = {
    methods: ["post"],
    url: '/spa/v1/queues/failed/{jobId}/retry',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::retry
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
*/
retry.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return retry.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::retry
* @see app/Http/Controllers/Spa/QueueController.php:130
* @route '/spa/v1/queues/failed/{jobId}/retry'
*/
retry.post = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: retry.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\QueueController::deleteMethod
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
*/
export const deleteMethod = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/spa/v1/queues/failed/{jobId}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\QueueController::deleteMethod
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
*/
deleteMethod.url = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return deleteMethod.definition.url
            .replace('{jobId}', parsedArgs.jobId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\QueueController::deleteMethod
* @see app/Http/Controllers/Spa/QueueController.php:157
* @route '/spa/v1/queues/failed/{jobId}'
*/
deleteMethod.delete = (args: { jobId: string | number } | [jobId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

const queues = {
    stats,
    failed,
    recent,
    retry,
    delete: deleteMethod,
}

export default queues