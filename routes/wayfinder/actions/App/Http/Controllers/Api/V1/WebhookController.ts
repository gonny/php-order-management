import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\WebhookController::index
* @see app/Http/Controllers/Api/V1/WebhookController.php:17
* @route '/api/v1/webhooks'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/webhooks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::index
* @see app/Http/Controllers/Api/V1/WebhookController.php:17
* @route '/api/v1/webhooks'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::index
* @see app/Http/Controllers/Api/V1/WebhookController.php:17
* @route '/api/v1/webhooks'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::index
* @see app/Http/Controllers/Api/V1/WebhookController.php:17
* @route '/api/v1/webhooks'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::receive
* @see app/Http/Controllers/Api/V1/WebhookController.php:64
* @route '/api/v1/webhooks/incoming/{source}'
*/
export const receive = (args: { source: string | number } | [source: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(args, options),
    method: 'post',
})

receive.definition = {
    methods: ["post"],
    url: '/api/v1/webhooks/incoming/{source}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::receive
* @see app/Http/Controllers/Api/V1/WebhookController.php:64
* @route '/api/v1/webhooks/incoming/{source}'
*/
receive.url = (args: { source: string | number } | [source: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { source: args }
    }

    if (Array.isArray(args)) {
        args = {
            source: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        source: args.source,
    }

    return receive.definition.url
            .replace('{source}', parsedArgs.source.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::receive
* @see app/Http/Controllers/Api/V1/WebhookController.php:64
* @route '/api/v1/webhooks/incoming/{source}'
*/
receive.post = (args: { source: string | number } | [source: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receive.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::balikovna
* @see app/Http/Controllers/Api/V1/WebhookController.php:100
* @route '/api/v1/webhooks/balikovna'
*/
export const balikovna = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: balikovna.url(options),
    method: 'post',
})

balikovna.definition = {
    methods: ["post"],
    url: '/api/v1/webhooks/balikovna',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::balikovna
* @see app/Http/Controllers/Api/V1/WebhookController.php:100
* @route '/api/v1/webhooks/balikovna'
*/
balikovna.url = (options?: RouteQueryOptions) => {
    return balikovna.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::balikovna
* @see app/Http/Controllers/Api/V1/WebhookController.php:100
* @route '/api/v1/webhooks/balikovna'
*/
balikovna.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: balikovna.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::dpd
* @see app/Http/Controllers/Api/V1/WebhookController.php:134
* @route '/api/v1/webhooks/dpd'
*/
export const dpd = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dpd.url(options),
    method: 'post',
})

dpd.definition = {
    methods: ["post"],
    url: '/api/v1/webhooks/dpd',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::dpd
* @see app/Http/Controllers/Api/V1/WebhookController.php:134
* @route '/api/v1/webhooks/dpd'
*/
dpd.url = (options?: RouteQueryOptions) => {
    return dpd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::dpd
* @see app/Http/Controllers/Api/V1/WebhookController.php:134
* @route '/api/v1/webhooks/dpd'
*/
dpd.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: dpd.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::payment
* @see app/Http/Controllers/Api/V1/WebhookController.php:168
* @route '/api/v1/webhooks/payment'
*/
export const payment = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payment.url(options),
    method: 'post',
})

payment.definition = {
    methods: ["post"],
    url: '/api/v1/webhooks/payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::payment
* @see app/Http/Controllers/Api/V1/WebhookController.php:168
* @route '/api/v1/webhooks/payment'
*/
payment.url = (options?: RouteQueryOptions) => {
    return payment.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\WebhookController::payment
* @see app/Http/Controllers/Api/V1/WebhookController.php:168
* @route '/api/v1/webhooks/payment'
*/
payment.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payment.url(options),
    method: 'post',
})

const WebhookController = { index, receive, balikovna, dpd, payment }

export default WebhookController