import type { ReactNode } from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '@/auth/AuthContext';

/**
 * Guards a route: unauthenticated users go to /login, and users lacking the
 * required permission get a clear 'not authorized' panel instead of a blank or
 * error-prone page.
 */
export function ProtectedRoute({ permission, children }: { permission?: string; children: ReactNode }) {
    const { token, loading, can } = useAuth();

    if (!token) {
        return <Navigate to="/login" replace />;
    }
    if (loading) {
        return <p className="p-6 text-sm text-slate-400">Loading…</p>;
    }
    if (permission && !can(permission)) {
        return (
            <div className="panel m-6">
                <h2>Not authorized</h2>
                <p className="mt-2 text-sm text-slate-400">
                    Your account does not have the <code>{permission}</code> permission.
                </p>
            </div>
        );
    }
    return <>{children}</>;
}
