import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\AuditLogController::index
* @see app/Http/Controllers/Spa/AuditLogController.php:15
* @route '/spa/v1/audit-logs'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/spa/v1/audit-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\AuditLogController::index
* @see app/Http/Controllers/Spa/AuditLogController.php:15
* @route '/spa/v1/audit-logs'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\AuditLogController::index
* @see app/Http/Controllers/Spa/AuditLogController.php:15
* @route '/spa/v1/audit-logs'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::index
* @see app/Http/Controllers/Spa/AuditLogController.php:15
* @route '/spa/v1/audit-logs'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::stats
* @see app/Http/Controllers/Spa/AuditLogController.php:96
* @route '/spa/v1/audit-logs/stats'
*/
export const stats = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

stats.definition = {
    methods: ["get","head"],
    url: '/spa/v1/audit-logs/stats',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\AuditLogController::stats
* @see app/Http/Controllers/Spa/AuditLogController.php:96
* @route '/spa/v1/audit-logs/stats'
*/
stats.url = (options?: RouteQueryOptions) => {
    return stats.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\AuditLogController::stats
* @see app/Http/Controllers/Spa/AuditLogController.php:96
* @route '/spa/v1/audit-logs/stats'
*/
stats.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stats.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::stats
* @see app/Http/Controllers/Spa/AuditLogController.php:96
* @route '/spa/v1/audit-logs/stats'
*/
stats.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stats.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::orderAuditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
export const orderAuditLogs = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orderAuditLogs.url(args, options),
    method: 'get',
})

orderAuditLogs.definition = {
    methods: ["get","head"],
    url: '/spa/v1/orders/{order}/audit-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\AuditLogController::orderAuditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
orderAuditLogs.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: args.order,
    }

    return orderAuditLogs.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\AuditLogController::orderAuditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
orderAuditLogs.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orderAuditLogs.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::orderAuditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
orderAuditLogs.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: orderAuditLogs.url(args, options),
    method: 'head',
})

const AuditLogController = { index, stats, orderAuditLogs }

export default AuditLogController