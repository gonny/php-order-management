import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Spa\ClientController::index
* @see app/Http/Controllers/Spa/ClientController.php:17
* @route '/spa/v1/clients'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/spa/v1/clients',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\ClientController::index
* @see app/Http/Controllers/Spa/ClientController.php:17
* @route '/spa/v1/clients'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\ClientController::index
* @see app/Http/Controllers/Spa/ClientController.php:17
* @route '/spa/v1/clients'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::index
* @see app/Http/Controllers/Spa/ClientController.php:17
* @route '/spa/v1/clients'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::store
* @see app/Http/Controllers/Spa/ClientController.php:62
* @route '/spa/v1/clients'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/spa/v1/clients',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Spa\ClientController::store
* @see app/Http/Controllers/Spa/ClientController.php:62
* @route '/spa/v1/clients'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\ClientController::store
* @see app/Http/Controllers/Spa/ClientController.php:62
* @route '/spa/v1/clients'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::show
* @see app/Http/Controllers/Spa/ClientController.php:92
* @route '/spa/v1/clients/{client}'
*/
export const show = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/spa/v1/clients/{client}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Spa\ClientController::show
* @see app/Http/Controllers/Spa/ClientController.php:92
* @route '/spa/v1/clients/{client}'
*/
show.url = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: args.client,
    }

    return show.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\ClientController::show
* @see app/Http/Controllers/Spa/ClientController.php:92
* @route '/spa/v1/clients/{client}'
*/
show.get = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::show
* @see app/Http/Controllers/Spa/ClientController.php:92
* @route '/spa/v1/clients/{client}'
*/
show.head = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::update
* @see app/Http/Controllers/Spa/ClientController.php:110
* @route '/spa/v1/clients/{client}'
*/
export const update = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/spa/v1/clients/{client}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Spa\ClientController::update
* @see app/Http/Controllers/Spa/ClientController.php:110
* @route '/spa/v1/clients/{client}'
*/
update.url = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: args.client,
    }

    return update.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\ClientController::update
* @see app/Http/Controllers/Spa/ClientController.php:110
* @route '/spa/v1/clients/{client}'
*/
update.put = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::update
* @see app/Http/Controllers/Spa/ClientController.php:110
* @route '/spa/v1/clients/{client}'
*/
update.patch = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Spa\ClientController::destroy
* @see app/Http/Controllers/Spa/ClientController.php:147
* @route '/spa/v1/clients/{client}'
*/
export const destroy = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/spa/v1/clients/{client}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Spa\ClientController::destroy
* @see app/Http/Controllers/Spa/ClientController.php:147
* @route '/spa/v1/clients/{client}'
*/
destroy.url = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: args.client,
    }

    return destroy.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Spa\ClientController::destroy
* @see app/Http/Controllers/Spa/ClientController.php:147
* @route '/spa/v1/clients/{client}'
*/
destroy.delete = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const ClientController = { index, store, show, update, destroy }

export default ClientController