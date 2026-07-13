/**
 * Abstraction over on-device secure storage. The app depends only on this
 * interface, so the token persistence path is unit-testable and the concrete
 * native backend (expo-secure-store / react-native-keychain) is swapped in at
 * the app entry point without touching business logic.
 */
export interface SecureStore {
    get(key: string): Promise<string | null>;
    set(key: string, value: string): Promise<void>;
    remove(key: string): Promise<void>;
}

/** Default backend for tests and as a fallback; not persistent across launches. */
export class InMemorySecureStore implements SecureStore {
    private readonly map = new Map<string, string>();

    async get(key: string): Promise<string | null> {
        return this.map.has(key) ? (this.map.get(key) as string) : null;
    }

    async set(key: string, value: string): Promise<void> {
        this.map.set(key, value);
    }

    async remove(key: string): Promise<void> {
        this.map.delete(key);
    }
}

export const SESSION_KEY = 'sms.session';

export interface StoredSession {
    baseUrl: string;
    token: string;
}

export async function loadSession(store: SecureStore): Promise<StoredSession | null> {
    const raw = await store.get(SESSION_KEY);
    if (!raw) return null;
    try {
        return JSON.parse(raw) as StoredSession;
    } catch {
        await store.remove(SESSION_KEY);
        return null;
    }
}

export async function saveSession(store: SecureStore, session: StoredSession): Promise<void> {
    await store.set(SESSION_KEY, JSON.stringify(session));
}

export async function clearSession(store: SecureStore): Promise<void> {
    await store.remove(SESSION_KEY);
}
