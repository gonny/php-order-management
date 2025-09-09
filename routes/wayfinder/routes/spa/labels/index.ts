import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\OrderController::voidMethod
* @see app/Http/Controllers/Spa/OrderController.php:366
* @route '/spa/v1/labels/{label}'
*/
export const voidMethod = (args: { label: string | number | { id: string | number } } | [label: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: voidMethod.url(args, options),
    method: 'delete',
})

voidMethod.definition = {
    methods: ["delete"],
    url: '/spa/v1/labels/{label}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\OrderController::voidMethod
* @see app/Http/Controllers/Spa/OrderController.php:366
* @route '/spa/v1/labels/{label}'
*/
voidMethod.url = (args: { label: string | number | { id: string | number } } | [label: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return voidMethod.definition.url
            .replace('{label}', parsedArgs.label.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\OrderController::voidMethod
* @see app/Http/Controllers/Spa/OrderController.php:366
* @route '/spa/v1/labels/{label}'
*/
voidMethod.delete = (args: { label: string | number | { id: string | number } } | [label: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: voidMethod.url(args, options),
    method: 'delete',
})

const labels = {
    void: voidMethod,
}

export default labels