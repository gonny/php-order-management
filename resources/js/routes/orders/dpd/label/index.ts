import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
export const download = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/label/dpd/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
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
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
download.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::download
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
download.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

const label = {
    download,
}

export default label