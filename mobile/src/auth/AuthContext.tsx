import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { ApiClient } from '@/lib/apiClient';
import { clearSession, loadSession, saveSession, type SecureStore, type StoredSession } from '@/lib/secureStore';
import { unregisterDeviceToken } from '@/lib/pushRegistration';
import type { Me } from '@/lib/types';

interface AuthState {
    session: StoredSession | null;
    me: Me | null;
    loading: boolean;
    api: ApiClient;
    login: (baseUrl: string, email: string, password: string) => Promise<void>;
    logout: (pushToken?: string | null) => Promise<void>;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ store, children }: { store: SecureStore; children: React.ReactNode }) {
    const [session, setSession] = useState<StoredSession | null>(null);
    const [me, setMe] = useState<Me | null>(null);
    const [loading, setLoading] = useState(true);

    const api = useMemo(
        () => new ApiClient({ baseUrl: session?.baseUrl ?? '', token: session?.token ?? null }),
        [session],
    );

    // Restore a persisted session on cold start.
    useEffect(() => {
        let cancelled = false;
        loadSession(store).then((restored) => {
            if (!cancelled) {
                setSession(restored);
                if (!restored) setLoading(false);
            }
        });
        return () => {
            cancelled = true;
        };
    }, [store]);

    // Resolve the profile whenever a session exists so screens can gate on roles.
    useEffect(() => {
        let cancelled = false;
        if (!session) {
            setMe(null);
            return;
        }
        setLoading(true);
        api.get<Me>('/auth/me')
            .then((profile) => {
                if (!cancelled) setMe(profile);
            })
            .catch(() => {
                if (!cancelled) {
                    void clearSession(store);
                    setSession(null);
                    setMe(null);
                }
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });
        return () => {
            cancelled = true;
        };
    }, [session, api, store]);

    const login = useCallback(
        async (baseUrl: string, email: string, password: string) => {
            const normalized = baseUrl.replace(/\/$/, '');
            const token = await new ApiClient({ baseUrl: normalized }).login('/auth/login', email, password);
            const next: StoredSession = { baseUrl: normalized, token };
            await saveSession(store, next);
            setSession(next);
        },
        [store],
    );

    const logout = useCallback(
        async (pushToken?: string | null) => {
            await unregisterDeviceToken(api, pushToken);
            try {
                await api.post('/auth/logout');
            } catch {
                // Best effort; proceed to clear local state regardless.
            }
            await clearSession(store);
            setSession(null);
            setMe(null);
        },
        [api, store],
    );

    const value: AuthState = { session, me, loading, api, login, logout };
    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
