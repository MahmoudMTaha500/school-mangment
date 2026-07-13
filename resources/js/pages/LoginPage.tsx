import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/lib/apiClient';
import { Icon } from '@/components/Icon';

const DEFAULT_BASE = 'http://green-valley.localhost:8080/api/v1';

export function LoginPage() {
    const { login, token } = useAuth(); const navigate = useNavigate();
    const [baseUrl, setBaseUrl] = useState(DEFAULT_BASE); const [kind, setKind] = useState<'tenant' | 'platform'>('tenant');
    const [email, setEmail] = useState(''); const [password, setPassword] = useState('');
    const [error, setError] = useState<string | null>(null); const [submitting, setSubmitting] = useState(false);
    useEffect(() => { if (token) navigate('/', { replace: true }); }, [token, navigate]);
    async function onSubmit(event: FormEvent) { event.preventDefault(); setError(null); setSubmitting(true); try { await login(baseUrl, kind, email, password); navigate('/', { replace: true }); } catch (err) { setError(err instanceof ApiError ? err.message : 'Sign in failed.'); } finally { setSubmitting(false); } }
    return <main className="login-page">
        <section className="login-showcase">
            <div className="login-brand"><span className="brand-mark"><span>e</span></span><div><strong>Eduvera</strong><small>School workspace</small></div></div>
            <div className="login-message"><span><Icon name="sparkles" /> Built for better schools</span><h1>One workspace.<br /><em>Every school moment.</em></h1><p>Bring your people, finances, and daily operations together in one calm, connected place.</p></div>
            <div className="login-trust"><div><Icon name="shield" /><span><strong>Secure by design</strong><small>Session-protected access</small></span></div><div><Icon name="overview" /><span><strong>Everything connected</strong><small>One view across your school</small></span></div></div>
            <div className="login-glow login-glow-one" /><div className="login-glow login-glow-two" />
        </section>
        <section className="login-panel"><div className="login-form-wrap animate-in">
            <div className="mobile-login-brand"><span className="brand-mark"><span>e</span></span><strong>Eduvera</strong></div>
            <span className="login-eyebrow">Welcome back</span><h2>Sign in to your workspace</h2><p>Enter your details to continue managing your school.</p>
            <form onSubmit={onSubmit} className="login-form" aria-label="Sign in">
                <label><span>Account type</span><select className="field" value={kind} onChange={(e) => setKind(e.target.value as 'tenant' | 'platform')}><option value="tenant">School user</option><option value="platform">Platform admin</option></select></label>
                <label><span>API base URL</span><input className="field" value={baseUrl} onChange={(e) => setBaseUrl(e.target.value)} required /></label>
                <label><span>Email</span><input className="field" type="email" placeholder="you@school.edu" value={email} onChange={(e) => setEmail(e.target.value)} required /></label>
                <label><span>Password</span><input className="field" type="password" placeholder="Enter your password" value={password} onChange={(e) => setPassword(e.target.value)} required /></label>
                {error && <div role="alert" className="login-error"><strong>Unable to sign in</strong><span>{error}</span></div>}
                <button className="primary login-submit" type="submit" disabled={submitting}>{submitting ? 'Signing in…' : <><span>Sign in</span><Icon name="arrow" /></>}</button>
            </form>
            <div className="session-note"><Icon name="shield" /> Your session token stays safely in this browser tab.</div>
        </div></section>
    </main>;
}
