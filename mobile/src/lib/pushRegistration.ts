import type { ApiClient } from './apiClient';

export type DevicePlatform = 'ios' | 'android';

/**
 * Registers a push token with the tenant API's device-token endpoint
 * (POST /me/device-tokens, built in Phase 3). Returns false when there is no
 * token to register (e.g. the user denied push permission) so callers can treat
 * a missing token as a no-op rather than an error.
 */
export async function registerDeviceToken(
    api: ApiClient,
    token: string | null | undefined,
    platform: DevicePlatform,
): Promise<boolean> {
    if (!token) {
        return false;
    }
    await api.post('/me/device-tokens', { token, platform });
    return true;
}

/** Best-effort removal on logout so a signed-out device stops receiving pushes. */
export async function unregisterDeviceToken(api: ApiClient, token: string | null | undefined): Promise<void> {
    if (!token) {
        return;
    }
    try {
        await api.delete('/me/device-tokens', { token });
    } catch {
        // A failed unregister must not block logout.
    }
}
