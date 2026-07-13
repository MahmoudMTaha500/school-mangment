import { describe, expect, it } from 'vitest';
import { clearSession, InMemorySecureStore, loadSession, saveSession, SESSION_KEY } from './secureStore';

describe('session persistence', () => {
    it('round-trips a saved session', async () => {
        const store = new InMemorySecureStore();
        await saveSession(store, { baseUrl: 'https://s/api/v1', token: 't1' });

        const restored = await loadSession(store);
        expect(restored).toEqual({ baseUrl: 'https://s/api/v1', token: 't1' });
    });

    it('returns null and clears corrupt data', async () => {
        const store = new InMemorySecureStore();
        await store.set(SESSION_KEY, 'not-json');

        expect(await loadSession(store)).toBeNull();
        expect(await store.get(SESSION_KEY)).toBeNull();
    });

    it('clears a session on logout', async () => {
        const store = new InMemorySecureStore();
        await saveSession(store, { baseUrl: 'https://s', token: 't' });
        await clearSession(store);

        expect(await loadSession(store)).toBeNull();
    });
});
