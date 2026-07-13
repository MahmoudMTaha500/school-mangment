
import { StyleSheet, Text } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/lib/useAsync';
import { formatDate } from '@/lib/format';
import type { Homework, MobileProfile, Paginated } from '@/lib/types';
import { AsyncBody, Card, colors, Muted, Screen } from './ui';

export function HomeworkScreen({ studentId }: { studentId: number | null }) {
    const { api } = useAuth();
    // Fall back to the signed-in student's own id when no child is selected.
    const profile = useAsync<MobileProfile>(() => api.get<MobileProfile>('/me'), []);
    const effectiveId = studentId ?? profile.data?.student?.id ?? null;

    const homework = useAsync<Paginated<Homework>>(
        () =>
            effectiveId
                ? api.getPaginated<Homework>(`/me/homework?student_id=${effectiveId}`)
                : Promise.resolve({ items: [], meta: null }),
        [effectiveId],
    );

    if (!profile.loading && effectiveId === null) {
        return (
            <Screen title="Homework">
                <Muted>Select a child from the Home tab to view their homework.</Muted>
            </Screen>
        );
    }

    return (
        <Screen title="Homework">
            <AsyncBody
                loading={profile.loading || homework.loading}
                error={homework.error}
                empty={homework.data?.items.length === 0}
            >
                {homework.data?.items.map((item) => (
                    <Card key={item.id}>
                        <Text style={styles.title}>{item.title}</Text>
                        <Text style={styles.meta}>
                            Due {formatDate(item.due_at)} · {item.status}
                        </Text>
                    </Card>
                ))}
            </AsyncBody>
        </Screen>
    );
}

const styles = StyleSheet.create({
    title: { color: colors.text, fontSize: 16, fontWeight: '600' },
    meta: { color: colors.muted, marginTop: 4 },
});
