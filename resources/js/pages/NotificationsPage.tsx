import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { formatDate } from '@/lib/format';
import type { AppNotification, Paginated } from '@/types/api';
import { EmptyState, ErrorState, LoadingState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';

export function NotificationsPage() {
    const { api } = useAuth();
    const notifications = useAsync<Paginated<AppNotification>>(() => api.getPaginated<AppNotification>('/notifications'), []);
    async function markRead(id: string) { await api.patch(`/notifications/${id}/read`); notifications.reload(); }

    return <div className="inner-page">
        <PageHeader eyebrow="Activity center" title="Notifications" description="Stay on top of important school updates and alerts." icon="notifications" />
        {notifications.loading && <LoadingState label="Loading notifications…" />}{notifications.error && <ErrorState message={notifications.error} />}
        <section className="notification-list animate-in delay-one">{notifications.data?.items.map((item) => <article key={item.id} className={`notification-card ${item.read_at ? 'is-read' : ''}`}>
            <span className="notification-type-icon"><Icon name="notifications" /></span>
            <div className="notification-copy"><span>{item.type}</span><strong>{String(item.data.message ?? item.type)}</strong><small><Icon name="clock" /> {formatDate(item.created_at)}</small></div>
            {!item.read_at && <button className="mark-read-button" onClick={() => void markRead(item.id)}><Icon name="check" /> Mark as read</button>}
        </article>)}</section>
        {notifications.data && notifications.data.items.length === 0 && <EmptyState icon="notifications" title="You are all caught up" description="New school updates will appear here." />}
    </div>;
}
