import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\WebhookController::index
* @see app/Http/Controllers/Spa/WebhookController.php:17
* @route '/spa/v1/webhooks'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/spa/v1/webhooks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\WebhookController::index
* @see app/Http/Controllers/Spa/WebhookController.php:17
* @route '/spa/v1/webhooks'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\WebhookController::index
* @see app/Http/Controllers/Spa/WebhookController.php:17
* @route '/spa/v1/webhooks'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\WebhookController::index
* @see app/Http/Controllers/Spa/WebhookController.php:17
* @route '/spa/v1/webhooks'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\WebhookController::show
* @see app/Http/Controllers/Spa/WebhookController.php:61
* @route '/spa/v1/webhooks/{webhook}'
*/
export const show = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/spa/v1/webhooks/{webhook}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\WebhookController::show
* @see app/Http/Controllers/Spa/WebhookController.php:61
* @route '/spa/v1/webhooks/{webhook}'
*/
show.url = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { webhook: args }
    }

    if (Array.isArray(args)) {
        args = {
            webhook: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        webhook: args.webhook,
    }

    return show.definition.url
            .replace('{webhook}', parsedArgs.webhook.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\WebhookController::show
* @see app/Http/Controllers/Spa/WebhookController.php:61
* @route '/spa/v1/webhooks/{webhook}'
*/
show.get = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\WebhookController::show
* @see app/Http/Controllers/Spa/WebhookController.php:61
* @route '/spa/v1/webhooks/{webhook}'
*/
show.head = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\WebhookController::reprocess
* @see app/Http/Controllers/Spa/WebhookController.php:73
* @route '/spa/v1/webhooks/{webhook}/reprocess'
*/
export const reprocess = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reprocess.url(args, options),
    method: 'post',
})

reprocess.definition = {
    methods: ["post"],
    url: '/spa/v1/webhooks/{webhook}/reprocess',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\WebhookController::reprocess
* @see app/Http/Controllers/Spa/WebhookController.php:73
* @route '/spa/v1/webhooks/{webhook}/reprocess'
*/
reprocess.url = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { webhook: args }
    }

    if (Array.isArray(args)) {
        args = {
            webhook: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        webhook: args.webhook,
    }

    return reprocess.definition.url
            .replace('{webhook}', parsedArgs.webhook.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\WebhookController::reprocess
* @see app/Http/Controllers/Spa/WebhookController.php:73
* @route '/spa/v1/webhooks/{webhook}/reprocess'
*/
reprocess.post = (args: { webhook: string | number } | [webhook: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reprocess.url(args, options),
    method: 'post',
})

const WebhookController = { index, show, reprocess }

export default WebhookController