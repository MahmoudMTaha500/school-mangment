import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { formatDate } from '@/lib/format';
import type { AuditLogEntry, Paginated } from '@/types/api';
import { EmptyState, ErrorState, LoadingState, PageHeader } from '@/components/PageHeader';

export function AuditLogsPage() {
    const { api } = useAuth();
    const logs = useAsync<Paginated<AuditLogEntry>>(() => api.getPaginated<AuditLogEntry>('/audit-logs'), []);
    return <div className="inner-page">
        <PageHeader eyebrow="Security center" title="Audit log" description="Review recent actions and monitor workspace security." icon="audit" />
        {logs.loading && <LoadingState label="Loading security activity…" />}{logs.error && <ErrorState message={logs.error} />}
        <section className="content-card animate-in delay-one">
            <div className="card-heading"><div><span>Recorded events</span><h2>Recent activity</h2></div><strong className="count-badge">{logs.data?.meta?.total ?? 0} events</strong></div>
            <div className="modern-table-wrap"><table className="modern-table"><thead><tr><th>Method</th><th>Path</th><th>Status</th><th>When</th></tr></thead><tbody>
                {logs.data?.items.map((entry) => <tr key={entry.id}><td><span className={`method-badge method-${entry.method.toLowerCase()}`}>{entry.method}</span></td><td className="path-cell">{entry.path}</td><td><span className={`http-status ${entry.status < 400 ? 'success' : 'failure'}`}>{entry.status}</span></td><td className="muted-cell">{formatDate(entry.created_at)}</td></tr>)}
            </tbody></table></div>
            {logs.data && logs.data.items.length === 0 && <EmptyState icon="audit" title="No recorded activity" description="Workspace changes will be recorded here." />}
        </section>
    </div>;
}
