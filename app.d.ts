/// <reference types="svelte" />
/// <reference types="vite/client" />

import type { Page, PageProps, Errors, ErrorBag } from '@inertiajs/core';
import type { route as routeFn } from './routes/wayfinder';

declare module '@inertiajs/core' {
  interface PageProps {
    auth: {
      user: App.Models.User | null;
    };
    flash: {
      success?: string;
      error?: string;
      warning?: string;
      info?: string;
    };
    errors: Errors & ErrorBag;
    // Add Wayfinder route helper to props if needed
    routes?: typeof routeFn;
  }
}

declare global {
  interface Window {
    // Wayfinder route helper
    route: typeof routeFn;
    // Any other global Laravel/Inertia utilities
    Laravel?: {
      csrfToken: string;
      user?: App.Models.User;
    };
  }
  
  namespace App {
    namespace Models {
    }
    
    namespace Data {
      // DTOs from Laravel Data package if used
    }
  }
  
  // Vite environment variables
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    readonly VITE_PUSHER_APP_KEY?: string;
    readonly VITE_PUSHER_HOST?: string;
    readonly VITE_PUSHER_PORT?: string;
    readonly VITE_PUSHER_SCHEME?: string;
    readonly VITE_PUSHER_APP_CLUSTER?: string;
  }
  
  interface ImportMeta {
    readonly env: ImportMetaEnv;
  }
}

// Ensure this is treated as a module
export {};
