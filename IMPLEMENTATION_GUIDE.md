# 🚀 Implementation Guide: Dual Authentication Pattern

This guide provides step-by-step instructions for implementing the recommended dual authentication pattern in your Svelte + Laravel 12 order management system.

## 🎯 Quick Start

If you want to implement the solution immediately, follow these condensed steps:

### 1. Install Laravel Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2. Configure Sanctum
Add to `config/sanctum.php`:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

Add to `app/Http/Kernel.php`:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 3. Create SPA Routes
Create `routes/spa.php`:
```php
<?php
use App\Http\Controllers\Spa\AuthController;
use App\Http\Controllers\Spa\OrderController;

// Authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Protected SPA routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('clients', ClientController::class);
});
```

### 4. Update Frontend API Client
Replace your HMAC client with this Sanctum client in `resources/js/lib/api.ts`:

```typescript
export class ApiClient {
    private baseUrl: string;

    constructor(baseUrl: string) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
    }

    async request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
        // Get CSRF token if needed
        await this.ensureCSRFToken();

        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            credentials: 'include', // Essential for cookies
        });

        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }

        return response.json();
    }

    private async ensureCSRFToken(): Promise<void> {
        if (!this.hasCSRFToken()) {
            await fetch(`${this.baseUrl}/sanctum/csrf-cookie`, {
                credentials: 'include',
            });
        }
    }

    private hasCSRFToken(): boolean {
        return document.cookie.includes('XSRF-TOKEN');
    }

    // Standard methods
    get<T>(endpoint: string): Promise<T> {
        return this.request<T>(endpoint, { method: 'GET' });
    }

    post<T>(endpoint: string, data: any): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }
}
```

---

## 📁 Complete File Structure

Here's the complete file structure for the new authentication system:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Spa/
│   │   │   ├── AuthController.php
│   │   │   ├── OrderController.php
│   │   │   └── ClientController.php
│   │   └── Server/
│   │       ├── OrderController.php (HMAC)
│   │       └── WebhookController.php (HMAC)
│   └── Middleware/
│       ├── HmacAuthenticationMiddleware.php (existing)
│       └── EnsureSpaAuthentication.php (new)
├── Services/
│   ├── HmacApiClient.php
│   └── AuthenticationService.php
└── Models/
    ├── User.php
    └── ApiClient.php

resources/js/
├── lib/
│   ├── sanctum-api.ts
│   ├── hmac-api.ts (for server-to-server)
│   └── auth.ts
└── components/
    ├── auth/
    │   ├── LoginForm.svelte
    │   └── AuthGuard.svelte
    └── shared/
        └── ApiProvider.svelte

routes/
├── spa.php (new)
├── server.php (new)
├── api.php (updated)
└── web.php (existing)
```

---

## 🔧 Detailed Implementation

### Step 1: Backend Setup

#### 1.1 Create SPA Authentication Controller
```php
<?php
// app/Http/Controllers/Spa/AuthController.php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return response()->json([
            'user' => Auth::user(),
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout successful']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }
}
```

#### 1.2 Create SPA Order Controller
```php
<?php
// app/Http/Controllers/Spa/OrderController.php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['client', 'items'])
            ->when($request->get('status'), fn($q, $status) => $q->where('status', $status))
            ->when($request->get('client_id'), fn($q, $clientId) => $q->where('client_id', $clientId))
            ->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'order' => $order->load(['client', 'items', 'shipping_label'])
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create($validated);
        return response()->json(['order' => $order->load('client', 'items')], 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'notes' => 'sometimes|string|nullable',
        ]);

        $order->update($validated);
        return response()->json(['order' => $order->load('client', 'items')]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
```

### Step 2: Frontend Implementation

#### 2.1 Create Sanctum API Service
```typescript
// resources/js/lib/sanctum-api.ts

export class SanctumApiClient {
    private baseUrl: string;
    private csrfInitialized = false;

    constructor(baseUrl: string) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
    }

    async request<T>(endpoint: string, options: RequestOptions = {}): Promise<T> {
        await this.initializeCSRF();

        const url = `${this.baseUrl}${endpoint}`;
        const headers: HeadersInit = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        };

        const config: RequestInit = {
            ...options,
            headers,
            credentials: 'include',
        };

        const response = await fetch(url, config);

        if (!response.ok) {
            const error = await this.handleErrorResponse(response);
            throw error;
        }

        return response.json();
    }

    private async initializeCSRF(): Promise<void> {
        if (!this.csrfInitialized) {
            await fetch(`${this.baseUrl}/sanctum/csrf-cookie`, {
                credentials: 'include',
            });
            this.csrfInitialized = true;
        }
    }

    private async handleErrorResponse(response: Response): Promise<Error> {
        let errorMessage = `Request failed with status ${response.status}`;
        
        try {
            const errorData = await response.json();
            errorMessage = errorData.message || errorMessage;
        } catch {
            // Use default message if response is not JSON
        }

        return new ApiError(response.status, errorMessage);
    }

    // Convenience methods
    get<T>(endpoint: string, params?: Record<string, any>): Promise<T> {
        const url = params ? `${endpoint}?${new URLSearchParams(params)}` : endpoint;
        return this.request<T>(url, { method: 'GET' });
    }

    post<T>(endpoint: string, data?: any): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'POST',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    put<T>(endpoint: string, data?: any): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'PUT',
            body: data ? JSON.stringify(data) : undefined,
        });
    }

    delete<T>(endpoint: string): Promise<T> {
        return this.request<T>(endpoint, { method: 'DELETE' });
    }
}

interface RequestOptions extends Omit<RequestInit, 'headers'> {
    headers?: Record<string, string>;
}

export class ApiError extends Error {
    constructor(public status: number, message: string) {
        super(message);
        this.name = 'ApiError';
    }
}
```

#### 2.2 Create Authentication Service
```typescript
// resources/js/lib/auth.ts

import { SanctumApiClient, ApiError } from './sanctum-api';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export class AuthService {
    private api: SanctumApiClient;

    constructor(api: SanctumApiClient) {
        this.api = api;
    }

    async login(email: string, password: string): Promise<User> {
        const response = await this.api.post<{ user: User }>('/spa/auth/login', {
            email,
            password,
        });
        return response.user;
    }

    async logout(): Promise<void> {
        await this.api.post('/spa/auth/logout');
    }

    async getCurrentUser(): Promise<User | null> {
        try {
            const response = await this.api.get<{ user: User }>('/spa/auth/user');
            return response.user;
        } catch (error) {
            if (error instanceof ApiError && error.status === 401) {
                return null;
            }
            throw error;
        }
    }

    async checkAuthentication(): Promise<boolean> {
        try {
            await this.getCurrentUser();
            return true;
        } catch {
            return false;
        }
    }
}
```

#### 2.3 Create Svelte Authentication Components

```svelte
<!-- resources/js/components/auth/LoginForm.svelte -->
<script lang="ts">
    import { AuthService } from '../../lib/auth';
    import { SanctumApiClient } from '../../lib/sanctum-api';
    import { createEventDispatcher } from 'svelte';

    const dispatch = createEventDispatcher<{ success: { user: User } }>();
    
    const api = new SanctumApiClient('/api');
    const auth = new AuthService(api);

    let email = '';
    let password = '';
    let loading = false;
    let error = '';

    async function handleSubmit() {
        if (loading) return;
        
        loading = true;
        error = '';

        try {
            const user = await auth.login(email, password);
            dispatch('success', { user });
        } catch (err) {
            error = err instanceof Error ? err.message : 'Login failed';
        } finally {
            loading = false;
        }
    }
</script>

<form on:submit|preventDefault={handleSubmit} class="space-y-4">
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
            Email
        </label>
        <input
            id="email"
            type="email"
            bind:value={email}
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            data-testid="email"
        />
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
            Password
        </label>
        <input
            id="password"
            type="password"
            bind:value={password}
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            data-testid="password"
        />
    </div>

    {#if error}
        <div class="text-red-600 text-sm">{error}</div>
    {/if}

    <button
        type="submit"
        disabled={loading}
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50"
        data-testid="login-button"
    >
        {loading ? 'Signing in...' : 'Sign In'}
    </button>
</form>
```

```svelte
<!-- resources/js/components/auth/AuthGuard.svelte -->
<script lang="ts">
    import { onMount } from 'svelte';
    import { AuthService, type User } from '../../lib/auth';
    import { SanctumApiClient } from '../../lib/sanctum-api';
    import LoginForm from './LoginForm.svelte';

    const api = new SanctumApiClient('/api');
    const auth = new AuthService(api);

    let user: User | null = null;
    let loading = true;
    let showLogin = false;

    onMount(async () => {
        try {
            user = await auth.getCurrentUser();
        } catch (error) {
            console.error('Auth check failed:', error);
        } finally {
            loading = false;
            showLogin = !user;
        }
    });

    async function handleLoginSuccess(event: CustomEvent<{ user: User }>) {
        user = event.detail.user;
        showLogin = false;
    }

    async function handleLogout() {
        try {
            await auth.logout();
            user = null;
            showLogin = true;
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }
</script>

{#if loading}
    <div class="flex items-center justify-center h-screen">
        <div class="text-lg">Loading...</div>
    </div>
{:else if showLogin}
    <div class="flex items-center justify-center h-screen">
        <div class="w-full max-w-md">
            <h1 class="text-2xl font-bold mb-6 text-center">Sign In</h1>
            <LoginForm on:success={handleLoginSuccess} />
        </div>
    </div>
{:else}
    <div class="min-h-screen bg-gray-50">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold">Order Management</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">
                            Welcome, {user?.name}
                        </span>
                        <button
                            on:click={handleLogout}
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <slot {user} />
        </main>
    </div>
{/if}
```

### Step 3: Route Configuration

#### 3.1 Update API Routes
```php
<?php
// routes/api.php

use App\Http\Controllers\Spa\AuthController;
use App\Http\Controllers\Spa\OrderController as SpaOrderController;
use App\Http\Controllers\Spa\ClientController as SpaClientController;
use App\Http\Controllers\Server\OrderController as ServerOrderController;
use App\Http\Middleware\HmacAuthenticationMiddleware;

// SPA Authentication routes (no auth required)
Route::prefix('spa')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('auth/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    
    // Protected SPA routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('orders', SpaOrderController::class);
        Route::apiResource('clients', SpaClientController::class);
    });
});

// Server-to-server routes (HMAC authentication)
Route::prefix('server')->middleware([
    HmacAuthenticationMiddleware::class,
    'throttle:120,1'
])->group(function () {
    Route::post('orders/{order}/pdf', [ServerOrderController::class, 'generatePdf']);
    Route::post('webhooks/incoming/{source}', [WebhookController::class, 'receive']);
});
```

#### 3.2 Configure CORS
```php
<?php
// config/cors.php

return [
    'paths' => ['api/*', 'spa/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Essential for cookies
];
```

### Step 4: Environment Configuration

#### 4.1 Update .env
```env
# Add these to your .env file
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:5173,127.0.0.1:5173
SESSION_DRIVER=database
SESSION_DOMAIN=localhost
FRONTEND_URL=http://localhost:5173
```

#### 4.2 Update Sanctum Config
```php
<?php
// config/sanctum.php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : '',
        env('FRONTEND_URL') ? ','.parse_url(env('FRONTEND_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'token_retrieval' => [
        'header' => 'Authorization',
        'prefix' => 'Bearer',
    ],

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],
];
```

---

## 🔍 Testing Your Implementation

### 1. Test SPA Authentication
```bash
# Test login
curl -X POST http://localhost:8000/api/spa/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "password": "password"}' \
  -c cookies.txt

# Test protected route
curl -X GET http://localhost:8000/api/spa/orders \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt
```

### 2. Test Server-to-Server (HMAC)
```bash
# Use your existing HMAC client for server routes
curl -X POST http://localhost:8000/api/server/orders/1/pdf \
  -H "X-Key-Id: your-key-id" \
  -H "X-Signature: your-signature" \
  -H "X-Timestamp: $(date +%s)" \
  -H "Digest: SHA-256=your-digest"
```

---

## 🚨 Common Issues & Solutions

### Issue 1: CORS Errors
**Solution**: Ensure `supports_credentials: true` in CORS config and `credentials: 'include'` in fetch requests.

### Issue 2: CSRF Token Missing
**Solution**: Always call `/sanctum/csrf-cookie` before authenticated requests.

### Issue 3: Session Not Persisting
**Solution**: Check that `SESSION_DOMAIN` matches your frontend domain.

### Issue 4: 419 CSRF Token Mismatch
**Solution**: Ensure XSRF-TOKEN cookie is being sent as X-XSRF-TOKEN header.

---

## 📋 Rollback Plan

If you need to rollback to the original HMAC-only system:

1. **Disable Sanctum routes**: Comment out SPA routes in `routes/api.php`
2. **Restore HMAC client**: Switch frontend back to HMAC authentication
3. **Update middleware**: Remove Sanctum middleware from API routes
4. **Clear sessions**: `php artisan session:clear`

---

This implementation guide provides everything needed to migrate from HMAC-only to the recommended dual authentication pattern. The new system will resolve your current authentication issues while providing a more maintainable and scalable foundation.