import { describe, expect, it, vi } from 'vitest';
import { ApiClient } from './apiClient';
import { registerDeviceToken, unregisterDeviceToken } from './pushRegistration';

function clientWith(fetchFn: typeof fetch): ApiClient {
    return new ApiClient({ baseUrl: 'https://x/api/v1', token: 't', fetchFn });
}

describe('registerDeviceToken', () => {
    it('posts the token and platform to the device-token endpoint', async () => {
        const fetchFn = vi.fn().mockImplementation(() => Promise.resolve(new Response('{}', { status: 201 })));
        const registered = await registerDeviceToken(clientWith(fetchFn), 'push-abc', 'android');

        expect(registered).toBe(true);
        const [url, init] = fetchFn.mock.calls[0];
        expect(url).toBe('https://x/api/v1/me/device-tokens');
        expect(JSON.parse(String(init.body))).toEqual({ token: 'push-abc', platform: 'android' });
    });

    it('is a no-op when there is no token (permission denied)', async () => {
        const fetchFn = vi.fn();
        const registered = await registerDeviceToken(clientWith(fetchFn as unknown as typeof fetch), null, 'ios');

        expect(registered).toBe(false);
        expect(fetchFn).not.toHaveBeenCalled();
    });
});

describe('unregisterDeviceToken', () => {
    it('swallows errors so logout is never blocked', async () => {
        const fetchFn = vi.fn().mockRejectedValue(new Error('network down'));
        await expect(unregisterDeviceToken(clientWith(fetchFn), 'push-abc')).resolves.toBeUndefined();
    });
});
