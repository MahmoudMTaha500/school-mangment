
import { StyleSheet, Text, View } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/lib/useAsync';
import type { AttendanceSummaryRow, MobileProfile } from '@/lib/types';
import { AsyncBody, Card, colors, Muted, Screen } from './ui';

export function AttendanceScreen({ studentId }: { studentId: number | null }) {
    const { api } = useAuth();
    const profile = useAsync<MobileProfile>(() => api.get<MobileProfile>('/me'), []);
    const effectiveId = studentId ?? profile.data?.student?.id ?? null;

    const summary = useAsync<AttendanceSummaryRow[]>(
        () =>
            effectiveId
                ? api.get<AttendanceSummaryRow[]>(`/me/attendance-summary?student_id=${effectiveId}`)
                : Promise.resolve([]),
        [effectiveId],
    );

    if (!profile.loading && effectiveId === null) {
        return (
            <Screen title="Attendance">
                <Muted>Select a child from the Home tab to view their attendance.</Muted>
            </Screen>
        );
    }

    return (
        <Screen title="Attendance" subtitle="Summary by status">
            <AsyncBody
                loading={profile.loading || summary.loading}
                error={summary.error}
                empty={summary.data?.length === 0}
            >
                <Card>
                    {summary.data?.map((row) => (
                        <View key={row.status} style={styles.row}>
                            <Text style={styles.status}>{row.status}</Text>
                            <Text style={styles.total}>{row.total}</Text>
                        </View>
                    ))}
                </Card>
            </AsyncBody>
        </Screen>
    );
}

const styles = StyleSheet.create({
    row: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderTopColor: colors.border, borderTopWidth: 1 },
    status: { color: colors.text, textTransform: 'capitalize' },
    total: { color: colors.accent, fontWeight: '700' },
});
