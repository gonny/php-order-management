import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
export const download = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
download.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return download.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
download.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
download.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PdfController::form
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
export const form = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(args, options),
    method: 'get',
})

form.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/pdf-generation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::form
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
form.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return form.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PdfController::form
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
form.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: form.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::form
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
form.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: form.url(args, options),
    method: 'head',
})

const pdf = {
    download,
    form,
}

export default pdf