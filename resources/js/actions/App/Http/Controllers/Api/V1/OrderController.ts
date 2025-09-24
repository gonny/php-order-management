import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\OrderController::index
* @see app/Http/Controllers/Api/V1/OrderController.php:31
* @route '/api/v1/orders'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::index
* @see app/Http/Controllers/Api/V1/OrderController.php:31
* @route '/api/v1/orders'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrderController::index
* @see app/Http/Controllers/Api/V1/OrderController.php:31
* @route '/api/v1/orders'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::index
* @see app/Http/Controllers/Api/V1/OrderController.php:31
* @route '/api/v1/orders'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::store
* @see app/Http/Controllers/Api/V1/OrderController.php:83
* @route '/api/v1/orders'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::store
* @see app/Http/Controllers/Api/V1/OrderController.php:83
* @route '/api/v1/orders'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrderController::store
* @see app/Http/Controllers/Api/V1/OrderController.php:83
* @route '/api/v1/orders'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::show
* @see app/Http/Controllers/Api/V1/OrderController.php:235
* @route '/api/v1/orders/{order}'
*/
export const show = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::show
* @see app/Http/Controllers/Api/V1/OrderController.php:235
* @route '/api/v1/orders/{order}'
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
* @see \App\Http\Controllers\Api\V1\OrderController::show
* @see app/Http/Controllers/Api/V1/OrderController.php:235
* @route '/api/v1/orders/{order}'
*/
show.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::show
* @see app/Http/Controllers/Api/V1/OrderController.php:235
* @route '/api/v1/orders/{order}'
*/
show.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::update
* @see app/Http/Controllers/Api/V1/OrderController.php:262
* @route '/api/v1/orders/{order}'
*/
export const update = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/api/v1/orders/{order}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::update
* @see app/Http/Controllers/Api/V1/OrderController.php:262
* @route '/api/v1/orders/{order}'
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
* @see \App\Http\Controllers\Api\V1\OrderController::update
* @see app/Http/Controllers/Api/V1/OrderController.php:262
* @route '/api/v1/orders/{order}'
*/
update.put = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::update
* @see app/Http/Controllers/Api/V1/OrderController.php:262
* @route '/api/v1/orders/{order}'
*/
update.patch = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::destroy
* @see app/Http/Controllers/Api/V1/OrderController.php:291
* @route '/api/v1/orders/{order}'
*/
export const destroy = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::destroy
* @see app/Http/Controllers/Api/V1/OrderController.php:291
* @route '/api/v1/orders/{order}'
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
* @see \App\Http\Controllers\Api\V1\OrderController::destroy
* @see app/Http/Controllers/Api/V1/OrderController.php:291
* @route '/api/v1/orders/{order}'
*/
destroy.delete = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::transition
* @see app/Http/Controllers/Api/V1/OrderController.php:313
* @route '/api/v1/orders/{order}/transitions/{transition}'
*/
export const transition = (args: { order: string | { id: string }, transition: string | number } | [order: string | { id: string }, transition: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: transition.url(args, options),
    method: 'post',
})

transition.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/transitions/{transition}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::transition
* @see app/Http/Controllers/Api/V1/OrderController.php:313
* @route '/api/v1/orders/{order}/transitions/{transition}'
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
* @see \App\Http\Controllers\Api\V1\OrderController::transition
* @see app/Http/Controllers/Api/V1/OrderController.php:313
* @route '/api/v1/orders/{order}/transitions/{transition}'
*/
transition.post = (args: { order: string | { id: string }, transition: string | number } | [order: string | { id: string }, transition: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: transition.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generateLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:362
* @route '/api/v1/orders/{order}/label'
*/
export const generateLabel = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLabel.url(args, options),
    method: 'post',
})

generateLabel.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/label',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generateLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:362
* @route '/api/v1/orders/{order}/label'
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
* @see \App\Http\Controllers\Api\V1\OrderController::generateLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:362
* @route '/api/v1/orders/{order}/label'
*/
generateLabel.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateLabel.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generateDpdLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:424
* @route '/api/v1/orders/{order}/label/dpd'
*/
export const generateDpdLabel = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDpdLabel.url(args, options),
    method: 'post',
})

generateDpdLabel.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/label/dpd',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generateDpdLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:424
* @route '/api/v1/orders/{order}/label/dpd'
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
* @see \App\Http\Controllers\Api\V1\OrderController::generateDpdLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:424
* @route '/api/v1/orders/{order}/label/dpd'
*/
generateDpdLabel.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateDpdLabel.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Api/V1/OrderController.php:508
* @route '/api/v1/orders/{order}/shipment/dpd'
*/
export const deleteDpdShipment = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteDpdShipment.url(args, options),
    method: 'delete',
})

deleteDpdShipment.definition = {
    methods: ["delete"],
    url: '/api/v1/orders/{order}/shipment/dpd',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Api/V1/OrderController.php:508
* @route '/api/v1/orders/{order}/shipment/dpd'
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
* @see \App\Http\Controllers\Api\V1\OrderController::deleteDpdShipment
* @see app/Http/Controllers/Api/V1/OrderController.php:508
* @route '/api/v1/orders/{order}/shipment/dpd'
*/
deleteDpdShipment.delete = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteDpdShipment.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Api/V1/OrderController.php:552
* @route '/api/v1/orders/{order}/tracking/refresh'
*/
export const refreshDpdTracking = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refreshDpdTracking.url(args, options),
    method: 'post',
})

refreshDpdTracking.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/tracking/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Api/V1/OrderController.php:552
* @route '/api/v1/orders/{order}/tracking/refresh'
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
* @see \App\Http\Controllers\Api\V1\OrderController::refreshDpdTracking
* @see app/Http/Controllers/Api/V1/OrderController.php:552
* @route '/api/v1/orders/{order}/tracking/refresh'
*/
refreshDpdTracking.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refreshDpdTracking.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdf
* @see app/Http/Controllers/Api/V1/OrderController.php:621
* @route '/api/v1/orders/{order}/pdf'
*/
export const generatePdf = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdf.url(args, options),
    method: 'post',
})

generatePdf.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/pdf',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdf
* @see app/Http/Controllers/Api/V1/OrderController.php:621
* @route '/api/v1/orders/{order}/pdf'
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
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdf
* @see app/Http/Controllers/Api/V1/OrderController.php:621
* @route '/api/v1/orders/{order}/pdf'
*/
generatePdf.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdf.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdfFromR2
* @see app/Http/Controllers/Api/V1/OrderController.php:687
* @route '/api/v1/orders/{order}/pdf/r2'
*/
export const generatePdfFromR2 = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdfFromR2.url(args, options),
    method: 'post',
})

generatePdfFromR2.definition = {
    methods: ["post"],
    url: '/api/v1/orders/{order}/pdf/r2',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdfFromR2
* @see app/Http/Controllers/Api/V1/OrderController.php:687
* @route '/api/v1/orders/{order}/pdf/r2'
*/
generatePdfFromR2.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return generatePdfFromR2.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrderController::generatePdfFromR2
* @see app/Http/Controllers/Api/V1/OrderController.php:687
* @route '/api/v1/orders/{order}/pdf/r2'
*/
generatePdfFromR2.post = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generatePdfFromR2.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\OrderController::voidLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:404
* @route '/api/v1/labels/{label}'
*/
export const voidLabel = (args: { label: string | { id: string } } | [label: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: voidLabel.url(args, options),
    method: 'delete',
})

voidLabel.definition = {
    methods: ["delete"],
    url: '/api/v1/labels/{label}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\OrderController::voidLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:404
* @route '/api/v1/labels/{label}'
*/
voidLabel.url = (args: { label: string | { id: string } } | [label: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { label: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { label: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            label: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        label: typeof args.label === 'object'
        ? args.label.id
        : args.label,
    }

    return voidLabel.definition.url
            .replace('{label}', parsedArgs.label.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\OrderController::voidLabel
* @see app/Http/Controllers/Api/V1/OrderController.php:404
* @route '/api/v1/labels/{label}'
*/
voidLabel.delete = (args: { label: string | { id: string } } | [label: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: voidLabel.url(args, options),
    method: 'delete',
})

const OrderController = { index, store, show, update, destroy, transition, generateLabel, generateDpdLabel, deleteDpdShipment, refreshDpdTracking, generatePdf, generatePdfFromR2, voidLabel }

export default OrderController