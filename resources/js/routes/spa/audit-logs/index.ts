import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
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

const auditLogs = {
    index,
    stats,
}

export default auditLogs