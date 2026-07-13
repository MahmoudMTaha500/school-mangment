import { useState, type FormEvent } from 'react';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/lib/apiClient';
import { ErrorState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';

export function ReportsPage() {
    const { api } = useAuth();
    const [from, setFrom] = useState('2026-01-01'); const [to, setTo] = useState('2026-12-31');
    const [result, setResult] = useState<unknown>(null); const [error, setError] = useState<string | null>(null); const [loading, setLoading] = useState(false);
    async function run(event: FormEvent) { event.preventDefault(); setError(null); setLoading(true); try { setResult(await api.get<unknown>(`/reports/wallet?from=${from}&to=${to}`)); } catch (err) { setError(err instanceof ApiError ? err.message : 'Report failed.'); } finally { setLoading(false); } }
    return <div className="inner-page">
        <PageHeader eyebrow="Analytics center" title="Reports" description="Generate financial summaries for any reporting period." icon="reports" />
        <section className="content-card report-builder animate-in delay-one">
            <div className="card-heading"><div><span>Wallet analytics</span><h2>Build a report</h2></div><div className="heading-chip"><Icon name="calendar" /> Date range</div></div>
            <form onSubmit={run} className="modern-form report-form" aria-label="Wallet report">
                <label><span>Start date</span><input className="field" type="date" value={from} onChange={(e) => setFrom(e.target.value)} /></label>
                <label><span>End date</span><input className="field" type="date" value={to} onChange={(e) => setTo(e.target.value)} /></label>
                <div className="report-action"><button className="primary form-submit" disabled={loading}>{loading ? 'Generating…' : <><Icon name="reports" /> Generate report</>}</button></div>
            </form>
            {error && <ErrorState message={error} />}
            {result != null && <div className="report-result"><div><span>Generated result</span><strong>Wallet summary</strong></div><pre>{JSON.stringify(result, null, 2)}</pre></div>}
        </section>
    </div>;
}
