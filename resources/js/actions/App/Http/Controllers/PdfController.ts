import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PdfController::downloadOrderPdf
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
export const downloadOrderPdf = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadOrderPdf.url(args, options),
    method: 'get',
})

downloadOrderPdf.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::downloadOrderPdf
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
downloadOrderPdf.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return downloadOrderPdf.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PdfController::downloadOrderPdf
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
downloadOrderPdf.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadOrderPdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::downloadOrderPdf
* @see app/Http/Controllers/PdfController.php:16
* @route '/orders/{order}/pdf'
*/
downloadOrderPdf.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadOrderPdf.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PdfController::showGenerationForm
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
export const showGenerationForm = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showGenerationForm.url(args, options),
    method: 'get',
})

showGenerationForm.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/pdf-generation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::showGenerationForm
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
showGenerationForm.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return showGenerationForm.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PdfController::showGenerationForm
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
showGenerationForm.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showGenerationForm.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::showGenerationForm
* @see app/Http/Controllers/PdfController.php:48
* @route '/orders/{order}/pdf-generation'
*/
showGenerationForm.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showGenerationForm.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PdfController::downloadDpdLabel
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
export const downloadDpdLabel = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadDpdLabel.url(args, options),
    method: 'get',
})

downloadDpdLabel.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/label/dpd/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PdfController::downloadDpdLabel
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
downloadDpdLabel.url = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
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

    return downloadDpdLabel.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PdfController::downloadDpdLabel
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
downloadDpdLabel.get = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadDpdLabel.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PdfController::downloadDpdLabel
* @see app/Http/Controllers/PdfController.php:64
* @route '/orders/{order}/label/dpd/download'
*/
downloadDpdLabel.head = (args: { order: string | { id: string } } | [order: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadDpdLabel.url(args, options),
    method: 'head',
})

const PdfController = { downloadOrderPdf, showGenerationForm, downloadDpdLabel }

export default PdfController