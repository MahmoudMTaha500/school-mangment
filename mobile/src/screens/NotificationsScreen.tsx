
import { StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/lib/useAsync';
import { formatDate } from '@/lib/format';
import type { AppNotification, Paginated } from '@/lib/types';
import { AsyncBody, Card, colors, Screen } from './ui';

export function NotificationsScreen() {
    const { api } = useAuth();
    const notifications = useAsync<Paginated<AppNotification>>(
        () => api.getPaginated<AppNotification>('/notifications'),
        [],
    );

    async function markRead(id: string) {
        await api.patch(`/notifications/${id}/read`);
        notifications.reload();
    }

    return (
        <Screen title="Notifications">
            <AsyncBody
                loading={notifications.loading}
                error={notifications.error}
                empty={notifications.data?.items.length === 0}
            >
                {notifications.data?.items.map((item) => (
                    <Card key={item.id}>
                        <View style={styles.row}>
                            <View style={styles.grow}>
                                <Text style={[styles.message, item.read_at ? styles.read : null]}>
                                    {item.data.message ?? item.type}
                                </Text>
                                <Text style={styles.date}>{formatDate(item.created_at)}</Text>
                            </View>
                            {!item.read_at ? (
                                <TouchableOpacity onPress={() => void markRead(item.id)}>
                                    <Text style={styles.action}>Mark read</Text>
                                </TouchableOpacity>
                            ) : null}
                        </View>
                    </Card>
                ))}
            </AsyncBody>
        </Screen>
    );
}

const styles = StyleSheet.create({
    row: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
    grow: { flex: 1 },
    message: { color: colors.text, fontWeight: '500' },
    read: { color: colors.muted },
    date: { color: colors.muted, fontSize: 12, marginTop: 4 },
    action: { color: colors.accent, fontWeight: '600' },
});
