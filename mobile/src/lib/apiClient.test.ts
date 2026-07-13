import { describe, expect, it, vi } from 'vitest';
import { ApiClient, ApiError, normalizePaginated } from './apiClient';

function jsonResponse(body: unknown, status = 200): Response {
    return new Response(status === 204 ? null : JSON.stringify(body), { status });
}

describe('normalizePaginated', () => {
    it('reads the flat { data: [...] } envelope', () => {
        const result = normalizePaginated<{ id: number }>({ data: [{ id: 1 }] });
        expect(result.items).toHaveLength(1);
    });

    it('reads the nested paginator { data: { data: [...] } } envelope', () => {
        const result = normalizePaginated<{ id: number }>({ data: { data: [{ id: 7 }] } });
        expect(result.items[0].id).toBe(7);
    });
});

describe('ApiClient', () => {
    it('sends the bearer token and unwraps the data envelope', async () => {
        const fetchFn = vi.fn().mockImplementation(() => Promise.resolve(jsonResponse({ data: { id: 5 } })));
        const client = new ApiClient({ baseUrl: 'https://x/api/v1/', token: 'tok', fetchFn });

        const result = await client.get<{ id: number }>('/me');

        expect(result.id).toBe(5);
        const [url, init] = fetchFn.mock.calls[0];
        expect(url).toBe('https://x/api/v1/me');
        expect((init.headers as Record<string, string>).Authorization).toBe('Bearer tok');
    });

    it('returns the plain login token', async () => {
        const fetchFn = vi.fn().mockImplementation(() => Promise.resolve(jsonResponse({ token: 'abc' })));
        const client = new ApiClient({ baseUrl: 'https://x', fetchFn });

        await expect(client.login('/auth/login', 'a@b.c', 'pw')).resolves.toBe('abc');
    });

    it('throws a typed ApiError on failure', async () => {
        const fetchFn = vi.fn().mockImplementation(() => Promise.resolve(jsonResponse({ message: 'Nope.' }, 403)));
        const client = new ApiClient({ baseUrl: 'https://x', fetchFn });

        const error = await client.get('/wallet/me').catch((e: unknown) => e);
        expect(error).toBeInstanceOf(ApiError);
        expect((error as ApiError).status).toBe(403);
    });
});
