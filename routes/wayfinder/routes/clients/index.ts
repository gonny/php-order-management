import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\ClientController::index
* @see app/Http/Controllers/Api/V1/ClientController.php:16
* @route '/api/v1/clients'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/v1/clients',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ClientController::index
* @see app/Http/Controllers/Api/V1/ClientController.php:16
* @route '/api/v1/clients'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ClientController::index
* @see app/Http/Controllers/Api/V1/ClientController.php:16
* @route '/api/v1/clients'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::index
* @see app/Http/Controllers/Api/V1/ClientController.php:16
* @route '/api/v1/clients'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ClientWebController::index
* @see app/Http/Controllers/ClientWebController.php:15
* @route '/clients'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/clients',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientWebController::index
* @see app/Http/Controllers/ClientWebController.php:15
* @route '/clients'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientWebController::index
* @see app/Http/Controllers/ClientWebController.php:15
* @route '/clients'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientWebController::index
* @see app/Http/Controllers/ClientWebController.php:15
* @route '/clients'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::store
* @see app/Http/Controllers/Api/V1/ClientController.php:61
* @route '/api/v1/clients'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/clients',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\ClientController::store
* @see app/Http/Controllers/Api/V1/ClientController.php:61
* @route '/api/v1/clients'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ClientController::store
* @see app/Http/Controllers/Api/V1/ClientController.php:61
* @route '/api/v1/clients'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::show
* @see app/Http/Controllers/Api/V1/ClientController.php:100
* @route '/api/v1/clients/{client}'
*/
export const show = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/v1/clients/{client}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ClientController::show
* @see app/Http/Controllers/Api/V1/ClientController.php:100
* @route '/api/v1/clients/{client}'
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
* @see \App\Http\Controllers\Api\V1\ClientController::show
* @see app/Http/Controllers/Api/V1/ClientController.php:100
* @route '/api/v1/clients/{client}'
*/
show.get = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::show
* @see app/Http/Controllers/Api/V1/ClientController.php:100
* @route '/api/v1/clients/{client}'
*/
show.head = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ClientWebController::show
* @see app/Http/Controllers/ClientWebController.php:38
* @route '/clients/{client}'
*/
export const show = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/clients/{client}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientWebController::show
* @see app/Http/Controllers/ClientWebController.php:38
* @route '/clients/{client}'
*/
show.url = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { client: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: typeof args.client === 'object'
        ? args.client.id
        : args.client,
    }

    return show.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientWebController::show
* @see app/Http/Controllers/ClientWebController.php:38
* @route '/clients/{client}'
*/
show.get = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientWebController::show
* @see app/Http/Controllers/ClientWebController.php:38
* @route '/clients/{client}'
*/
show.head = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::update
* @see app/Http/Controllers/Api/V1/ClientController.php:118
* @route '/api/v1/clients/{client}'
*/
export const update = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/api/v1/clients/{client}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Api\V1\ClientController::update
* @see app/Http/Controllers/Api/V1/ClientController.php:118
* @route '/api/v1/clients/{client}'
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
* @see \App\Http\Controllers\Api\V1\ClientController::update
* @see app/Http/Controllers/Api/V1/ClientController.php:118
* @route '/api/v1/clients/{client}'
*/
update.put = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::update
* @see app/Http/Controllers/Api/V1/ClientController.php:118
* @route '/api/v1/clients/{client}'
*/
update.patch = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Api\V1\ClientController::destroy
* @see app/Http/Controllers/Api/V1/ClientController.php:166
* @route '/api/v1/clients/{client}'
*/
export const destroy = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/clients/{client}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\ClientController::destroy
* @see app/Http/Controllers/Api/V1/ClientController.php:166
* @route '/api/v1/clients/{client}'
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
* @see \App\Http\Controllers\Api\V1\ClientController::destroy
* @see app/Http/Controllers/Api/V1/ClientController.php:166
* @route '/api/v1/clients/{client}'
*/
destroy.delete = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\ClientWebController::create
* @see app/Http/Controllers/ClientWebController.php:30
* @route '/clients/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/clients/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientWebController::create
* @see app/Http/Controllers/ClientWebController.php:30
* @route '/clients/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientWebController::create
* @see app/Http/Controllers/ClientWebController.php:30
* @route '/clients/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientWebController::create
* @see app/Http/Controllers/ClientWebController.php:30
* @route '/clients/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ClientWebController::edit
* @see app/Http/Controllers/ClientWebController.php:52
* @route '/clients/{client}/edit'
*/
export const edit = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/clients/{client}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientWebController::edit
* @see app/Http/Controllers/ClientWebController.php:52
* @route '/clients/{client}/edit'
*/
edit.url = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { client: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: typeof args.client === 'object'
        ? args.client.id
        : args.client,
    }

    return edit.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientWebController::edit
* @see app/Http/Controllers/ClientWebController.php:52
* @route '/clients/{client}/edit'
*/
edit.get = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientWebController::edit
* @see app/Http/Controllers/ClientWebController.php:52
* @route '/clients/{client}/edit'
*/
edit.head = (args: { client: string | number | { id: string | number } } | [client: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

const clients = {
    index,
    store,
    show,
    update,
    destroy,
    create,
    edit,
}

export default clients