import type { Paginated, PaginationMeta } from '@/types/api';

export class ApiError extends Error {
    constructor(
        message: string,
        readonly status: number,
        readonly errors?: Record<string, string[]>,
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

export interface ApiClientOptions {
    baseUrl: string;
    token?: string | null;
    /** Injectable for tests; defaults to the global fetch. */
    fetchFn?: typeof fetch;
}

/**
 * Thin typed wrapper over fetch that understands the API's `{ data: ... }`
 * envelope, Sanctum bearer auth, and Laravel's 422 validation error shape.
 */
export class ApiClient {
    private readonly baseUrl: string;
    private readonly token?: string | null;
    private readonly fetchFn: typeof fetch;

    constructor(options: ApiClientOptions) {
        this.baseUrl = options.baseUrl.replace(/\/$/, '');
        this.token = options.token;
        this.fetchFn = options.fetchFn ?? fetch.bind(globalThis);
    }

    async login(path: '/auth/login' | '/platform/login', email: string, password: string): Promise<string> {
        const body = await this.request<{ token: string }>('POST', path, {
            email,
            password,
            device_name: 'admin-web',
        });
        return body.token;
    }

    /** Unwraps a single-object `{ data: T }` response. */
    async get<T>(path: string): Promise<T> {
        const body = await this.request<{ data: T }>('GET', path);
        return body.data;
    }

    /** Unwraps a paginated resource collection into items + meta. */
    async getPaginated<T>(path: string): Promise<Paginated<T>> {
        const body = await this.request<PaginatedResponse<T>>('GET', path);
        return normalizePaginated<T>(body);
    }

    async post<T>(path: string, payload?: unknown): Promise<T> {
        return this.request<T>('POST', path, payload);
    }

    async patch<T>(path: string, payload?: unknown): Promise<T> {
        return this.request<T>('PATCH', path, payload);
    }

    async put<T>(path: string, payload?: unknown): Promise<T> {
        return this.request<T>('PUT', path, payload);
    }

    async delete(path: string): Promise<void> {
        await this.request<null>('DELETE', path);
    }

    private async request<T>(method: string, path: string, payload?: unknown): Promise<T> {
        const headers: Record<string, string> = { Accept: 'application/json' };
        if (this.token) {
            headers.Authorization = `Bearer ${this.token}`;
        }
        if (payload !== undefined) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await this.fetchFn(`${this.baseUrl}${path}`, {
            method,
            headers,
            body: payload !== undefined ? JSON.stringify(payload) : undefined,
        });

        if (response.status === 204) {
            return null as T;
        }

        const text = await response.text();
        const parsed = text ? JSON.parse(text) : null;

        if (!response.ok) {
            const message = parsed?.message ?? `Request failed (${response.status})`;
            throw new ApiError(message, response.status, parsed?.errors);
        }

        return parsed as T;
    }
}

interface PaginatedResponse<T> {
    data: T[] | { data: T[]; meta?: PaginationMeta } | undefined;
    meta?: PaginationMeta;
}

// Laravel resource collections return `{ data: [...], meta }`. We also tolerate
// a nested `{ data: { data: [...] } }` shape defensively so a server-side change
// in envelope depth does not blank the UI.
export function normalizePaginated<T>(body: PaginatedResponse<T>): Paginated<T> {
    if (Array.isArray(body.data)) {
        return { items: body.data, meta: body.meta ?? null };
    }
    if (body.data && Array.isArray(body.data.data)) {
        return { items: body.data.data, meta: body.data.meta ?? body.meta ?? null };
    }
    return { items: [], meta: null };
}
