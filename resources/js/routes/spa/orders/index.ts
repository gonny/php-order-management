import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\OrderController::index
* @see app/Http/Controllers/Spa/OrderController.php:30
* @route '/spa/v1/orders'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/spa/v1/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::index
* @see app/Http/Controllers/Spa/OrderController.php:30
* @route '/spa/v1/orders'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::index
* @see app/Http/Controllers/Spa/OrderController.php:30
* @route '/spa/v1/orders'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::index
* @see app/Http/Controllers/Spa/OrderController.php:30
* @route '/spa/v1/orders'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::store
* @see app/Http/Controllers/Spa/OrderController.php:82
* @route '/spa/v1/orders'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/spa/v1/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::store
* @see app/Http/Controllers/Spa/OrderController.php:82
* @route '/spa/v1/orders'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::store
* @see app/Http/Controllers/Spa/OrderController.php:82
* @route '/spa/v1/orders'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::show
* @see app/Http/Controllers/Spa/OrderController.php:197
* @route '/spa/v1/orders/{order}'
*/
export const show = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/spa/v1/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::show
* @see app/Http/Controllers/Spa/OrderController.php:197
* @route '/spa/v1/orders/{order}'
*/
show.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::show
* @see app/Http/Controllers/Spa/OrderController.php:197
* @route '/spa/v1/orders/{order}'
*/
show.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::show
* @see app/Http/Controllers/Spa/OrderController.php:197
* @route '/spa/v1/orders/{order}'
*/
show.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::update
* @see app/Http/Controllers/Spa/OrderController.php:224
* @route '/spa/v1/orders/{order}'
*/
export const update = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/spa/v1/orders/{order}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::update
* @see app/Http/Controllers/Spa/OrderController.php:224
* @route '/spa/v1/orders/{order}'
*/
update.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return update.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::update
* @see app/Http/Controllers/Spa/OrderController.php:224
* @route '/spa/v1/orders/{order}'
*/
update.put = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::update
* @see app/Http/Controllers/Spa/OrderController.php:224
* @route '/spa/v1/orders/{order}'
*/
update.patch = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::destroy
* @see app/Http/Controllers/Spa/OrderController.php:253
* @route '/spa/v1/orders/{order}'
*/
export const destroy = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/spa/v1/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::destroy
* @see app/Http/Controllers/Spa/OrderController.php:253
* @route '/spa/v1/orders/{order}'
*/
destroy.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return destroy.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::destroy
* @see app/Http/Controllers/Spa/OrderController.php:253
* @route '/spa/v1/orders/{order}'
*/
destroy.delete = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::transition
* @see app/Http/Controllers/Spa/OrderController.php:275
* @route '/spa/v1/orders/{order}/transitions/{transition}'
*/
export const transition = (args: { order: string | { id: string }, transition: string | number } | [order: string | { id: string }, transition: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: transition.url(args, options),
    method: 'post',
})

transition.definition = {
    methods: ["post"],
    url: '/spa/v1/orders/{order}/transitions/{transition}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::transition
* @see app/Http/Controllers/Spa/OrderController.php:275
* @route '/spa/v1/orders/{order}/transitions/{transition}'
*/
transition.url = (args: { order: string | { id: string }, transition: string | number } | [order: string | { id: string }, transition: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            order: args[0],
            transition: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
        transition: args.transition,
    }

    return transition.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace('{transition}', parsedArgs.transition.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::transition
* @see app/Http/Controllers/Spa/OrderController.php:275
* @route '/spa/v1/orders/{order}/transitions/{transition}'
*/
transition.post = (args: { order: string | { id: string }, transition: string | number } | [order: string | { id: string }, transition: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: transition.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::generateLabel
* @see app/Http/Controllers/Spa/OrderController.php:324
* @route '/spa/v1/orders/{order}/label'
*/
export const generateLabel = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLabel.url(args, options),
    method: 'post',
})

generateLabel.definition = {
    methods: ["post"],
    url: '/spa/v1/orders/{order}/label',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::generateLabel
* @see app/Http/Controllers/Spa/OrderController.php:324
* @route '/spa/v1/orders/{order}/label'
*/
generateLabel.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return generateLabel.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::generateLabel
* @see app/Http/Controllers/Spa/OrderController.php:324
* @route '/spa/v1/orders/{order}/label'
*/
generateLabel.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLabel.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::generateDpdLabel
* @see app/Http/Controllers/Spa/OrderController.php:386
* @route '/spa/v1/orders/{order}/label/dpd'
*/
export const generateDpdLabel = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDpdLabel.url(args, options),
    method: 'post',
})

generateDpdLabel.definition = {
    methods: ["post"],
    url: '/spa/v1/orders/{order}/label/dpd',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::generateDpdLabel
* @see app/Http/Controllers/Spa/OrderController.php:386
* @route '/spa/v1/orders/{order}/label/dpd'
*/
generateDpdLabel.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return generateDpdLabel.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::generateDpdLabel
* @see app/Http/Controllers/Spa/OrderController.php:386
* @route '/spa/v1/orders/{order}/label/dpd'
*/
generateDpdLabel.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDpdLabel.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Spa/OrderController.php:470
* @route '/spa/v1/orders/{order}/shipment/dpd'
*/
export const deleteDpdShipment = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteDpdShipment.url(args, options),
    method: 'delete',
})

deleteDpdShipment.definition = {
    methods: ["delete"],
    url: '/spa/v1/orders/{order}/shipment/dpd',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Spa/OrderController.php:470
* @route '/spa/v1/orders/{order}/shipment/dpd'
*/
deleteDpdShipment.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return deleteDpdShipment.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Spa/OrderController.php:470
* @route '/spa/v1/orders/{order}/shipment/dpd'
*/
deleteDpdShipment.delete = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteDpdShipment.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Spa/OrderController.php:514
* @route '/spa/v1/orders/{order}/tracking/refresh'
*/
export const refreshDpdTracking = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refreshDpdTracking.url(args, options),
    method: 'post',
})

refreshDpdTracking.definition = {
    methods: ["post"],
    url: '/spa/v1/orders/{order}/tracking/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Spa/OrderController.php:514
* @route '/spa/v1/orders/{order}/tracking/refresh'
*/
refreshDpdTracking.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return refreshDpdTracking.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Spa/OrderController.php:514
* @route '/spa/v1/orders/{order}/tracking/refresh'
*/
refreshDpdTracking.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refreshDpdTracking.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\OrderController::generatePdf
* @see app/Http/Controllers/Spa/OrderController.php:583
* @route '/spa/v1/orders/{order}/pdf'
*/
export const generatePdf = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdf.url(args, options),
    method: 'post',
})

generatePdf.definition = {
    methods: ["post"],
    url: '/spa/v1/orders/{order}/pdf',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::generatePdf
* @see app/Http/Controllers/Spa/OrderController.php:583
* @route '/spa/v1/orders/{order}/pdf'
*/
generatePdf.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { order: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: typeof args.order === 'object'
        ? args.order.id
        : args.order,
    }

    return generatePdf.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::generatePdf
* @see app/Http/Controllers/Spa/OrderController.php:583
* @route '/spa/v1/orders/{order}/pdf'
*/
generatePdf.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdf.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::auditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
export const auditLogs = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: auditLogs.url(args, options),
    method: 'get',
})

auditLogs.definition = {
    methods: ["get","head"],
    url: '/spa/v1/orders/{order}/audit-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\AuditLogController::auditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
auditLogs.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return auditLogs.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\AuditLogController::auditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
auditLogs.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: auditLogs.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\AuditLogController::auditLogs
* @see app/Http/Controllers/Spa/AuditLogController.php:71
* @route '/spa/v1/orders/{order}/audit-logs'
*/
auditLogs.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: auditLogs.url(args, options),
    method: 'head',
})

const orders = {
    index,
    store,
    show,
    update,
    destroy,
    transition,
    generateLabel,
    generateDpdLabel,
    deleteDpdShipment,
    refreshDpdTracking,
    generatePdf,
    auditLogs,
}

export default orders