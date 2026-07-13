import { describe, expect, it, vi } from 'vitest';
import { ApiClient, ApiError, normalizePaginated } from './apiClient';

function jsonResponse(body: unknown, status = 200): Response {
    return new Response(status === 204 ? null : JSON.stringify(body), {
        status,
        headers: { 'Content-Type': 'application/json' },
    });
}

describe('normalizePaginated', () => {
    it('reads the standard { data: [...] , meta } envelope', () => {
        const result = normalizePaginated<{ id: number }>({
            data: [{ id: 1 }],
            meta: { current_page: 1, last_page: 1, total: 1, per_page: 30 },
        });
        expect(result.items).toHaveLength(1);
        expect(result.meta?.total).toBe(1);
    });

    it('tolerates a nested { data: { data: [...] } } envelope', () => {
        const result = normalizePaginated<{ id: number }>({ data: { data: [{ id: 9 }] } });
        expect(result.items[0].id).toBe(9);
    });

    it('returns an empty list for an unexpected shape', () => {
        const result = normalizePaginated<{ id: number }>({ data: undefined });
        expect(result.items).toEqual([]);
    });
});

describe('ApiClient', () => {
    it('unwraps a single-object data envelope and sends the bearer token', async () => {
        const fetchFn = vi.fn().mockResolvedValue(jsonResponse({ data: { id: 1, name: 'A' } }));
        const client = new ApiClient({ baseUrl: 'http://x/api/v1/', token: 'tok', fetchFn });

        const me = await client.get<{ id: number; name: string }>('/auth/me');

        expect(me.name).toBe('A');
        const [url, init] = fetchFn.mock.calls[0];
        expect(url).toBe('http://x/api/v1/auth/me');
        expect((init.headers as Record<string, string>).Authorization).toBe('Bearer tok');
    });

    it('returns the plain token from a login call', async () => {
        const fetchFn = vi.fn().mockResolvedValue(jsonResponse({ token: 'plain-text-token' }));
        const client = new ApiClient({ baseUrl: 'http://x/api/v1', fetchFn });

        const token = await client.login('/auth/login', 'a@b.c', 'secret');

        expect(token).toBe('plain-text-token');
        expect(fetchFn.mock.calls[0][1].method).toBe('POST');
    });

    it('throws a typed ApiError carrying validation errors on 422', async () => {
        const fetchFn = vi.fn().mockImplementation(() =>
            Promise.resolve(jsonResponse({ message: 'Invalid.', errors: { code: ['taken'] } }, 422)),
        );
        const client = new ApiClient({ baseUrl: 'http://x', fetchFn });

        const error = await client.post('/sis/students', {}).catch((e: unknown) => e);
        expect(error).toBeInstanceOf(ApiError);
        expect((error as ApiError).status).toBe(422);
        expect((error as ApiError).errors).toEqual({ code: ['taken'] });
    });

    it('treats 204 as an empty result', async () => {
        const fetchFn = vi.fn().mockResolvedValue(jsonResponse(null, 204));
        const client = new ApiClient({ baseUrl: 'http://x', token: 't', fetchFn });

        await expect(client.delete('/attachments/1')).resolves.toBeUndefined();
    });
});
