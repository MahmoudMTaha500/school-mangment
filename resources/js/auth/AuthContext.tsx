import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { ApiClient } from '@/lib/apiClient';
import type { Me } from '@/types/api';

interface Session {
    baseUrl: string;
    token: string;
}

interface AuthState {
    baseUrl: string | null;
    token: string | null;
    me: Me | null;
    loading: boolean;
    api: ApiClient;
    login: (baseUrl: string, kind: 'tenant' | 'platform', email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    can: (permission: string) => boolean;
    hasRole: (role: string) => boolean;
}

const STORAGE_KEY = 'sms.session';

function readSession(): Session | null {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return null;
    try {
        return JSON.parse(raw) as Session;
    } catch {
        return null;
    }
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
    const [session, setSession] = useState<Session | null>(() => readSession());
    const [me, setMe] = useState<Me | null>(null);
    const [loading, setLoading] = useState<boolean>(session !== null);

    const api = useMemo(
        () => new ApiClient({ baseUrl: session?.baseUrl ?? '', token: session?.token ?? null }),
        [session],
    );

    useEffect(() => {
        let cancelled = false;
        if (!session) {
            setMe(null);
            setLoading(false);
            return;
        }
        setLoading(true);
        api.get<Me>('/auth/me')
            .then((profile) => {
                if (!cancelled) setMe(profile);
            })
            .catch(() => {
                if (!cancelled) {
                    sessionStorage.removeItem(STORAGE_KEY);
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
    }, [session, api]);

    const login = useCallback(
        async (baseUrl: string, kind: 'tenant' | 'platform', email: string, password: string) => {
            const normalized = baseUrl.replace(/\/$/, '');
            const client = new ApiClient({ baseUrl: normalized });
            const token = await client.login(kind === 'platform' ? '/platform/login' : '/auth/login', email, password);
            const next: Session = { baseUrl: normalized, token };
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(next));
            setSession(next);
        },
        [],
    );

    const logout = useCallback(async () => {
        try {
            await api.post('/auth/logout');
        } catch {
        }
        sessionStorage.removeItem(STORAGE_KEY);
        setSession(null);
        setMe(null);
    }, [api]);

    const can = useCallback((permission: string) => me?.permissions.includes(permission) ?? false, [me]);
    const hasRole = useCallback((role: string) => me?.roles.includes(role) ?? false, [me]);

    const value: AuthState = {
        baseUrl: session?.baseUrl ?? null,
        token: session?.token ?? null,
        me,
        loading,
        api,
        login,
        logout,
        can,
        hasRole,
    };

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}
