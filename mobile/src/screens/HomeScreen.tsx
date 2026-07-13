
import { StyleSheet, Text, TouchableOpacity } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/lib/useAsync';
import type { Child, MobileProfile, Paginated } from '@/lib/types';
import { AsyncBody, Card, colors, Screen } from './ui';

export function HomeScreen({ onSelectChild }: { onSelectChild: (studentId: number) => void }) {
    const { api, me } = useAuth();
    const profile = useAsync<MobileProfile>(() => api.get<MobileProfile>('/me'), []);
    const isParent = me?.roles.includes('parent') ?? false;
    const children = useAsync<Paginated<Child>>(
        () => (isParent ? api.getPaginated<Child>('/me/children') : Promise.resolve({ items: [], meta: null })),
        [isParent],
    );

    return (
        <Screen title={`Hello, ${profile.data?.name ?? ''}`} subtitle={me?.roles.join(', ')}>
            <AsyncBody loading={profile.loading} error={profile.error}>
                {isParent ? (
                    <>
                        <Text style={styles.section}>Your children</Text>
                        <AsyncBody
                            loading={children.loading}
                            error={children.error}
                            empty={children.data?.items.length === 0}
                        >
                            {children.data?.items.map((child) => (
                                <TouchableOpacity key={child.id} onPress={() => onSelectChild(child.id)}>
                                    <Card>
                                        <Text style={styles.childName}>
                                            {child.first_name} {child.last_name}
                                        </Text>
                                        <Text style={styles.muted}>Tap to view homework & attendance</Text>
                                    </Card>
                                </TouchableOpacity>
                            ))}
                        </AsyncBody>
                    </>
                ) : (
                    <Card>
                        <Text style={styles.muted}>Signed in as a student.</Text>
                        <Text style={styles.childName}>Use the tabs to view your homework, attendance and wallet.</Text>
                    </Card>
                )}
            </AsyncBody>
        </Screen>
    );
}

const styles = StyleSheet.create({
    section: { color: colors.muted, fontSize: 13, textTransform: 'uppercase', letterSpacing: 1 },
    childName: { color: colors.text, fontSize: 16, fontWeight: '600' },
    muted: { color: colors.muted, marginTop: 4 },
});
