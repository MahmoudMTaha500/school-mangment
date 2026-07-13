import { useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { AuthProvider, useAuth } from '@/auth/AuthContext';
import { nativeSecureStore } from '@/lib/secureStoreNative';
import { LoginScreen } from '@/screens/LoginScreen';
import { HomeScreen } from '@/screens/HomeScreen';
import { HomeworkScreen } from '@/screens/HomeworkScreen';
import { AttendanceScreen } from '@/screens/AttendanceScreen';
import { WalletScreen } from '@/screens/WalletScreen';
import { NotificationsScreen } from '@/screens/NotificationsScreen';
import { colors } from '@/screens/ui';

type Tab = 'home' | 'homework' | 'attendance' | 'wallet' | 'notifications';

const TABS: { key: Tab; label: string }[] = [
    { key: 'home', label: 'Home' },
    { key: 'homework', label: 'Homework' },
    { key: 'attendance', label: 'Attendance' },
    { key: 'wallet', label: 'Wallet' },
    { key: 'notifications', label: 'Alerts' },
];

function Shell() {
    const { logout } = useAuth();
    const [tab, setTab] = useState<Tab>('home');
    // A parent selects a child on the Home tab; that scopes the student views.
    const [selectedStudentId, setSelectedStudentId] = useState<number | null>(null);

    function selectChild(studentId: number) {
        setSelectedStudentId(studentId);
        setTab('homework');
    }

    return (
        <View style={styles.shell}>
            <View style={styles.header}>
                <Text style={styles.brand}>School</Text>
                <TouchableOpacity onPress={() => void logout()}>
                    <Text style={styles.signOut}>Sign out</Text>
                </TouchableOpacity>
            </View>

            <View style={styles.content}>
                {tab === 'home' && <HomeScreen onSelectChild={selectChild} />}
                {tab === 'homework' && <HomeworkScreen studentId={selectedStudentId} />}
                {tab === 'attendance' && <AttendanceScreen studentId={selectedStudentId} />}
                {tab === 'wallet' && <WalletScreen />}
                {tab === 'notifications' && <NotificationsScreen />}
            </View>

            <View style={styles.tabBar}>
                {TABS.map((item) => (
                    <TouchableOpacity key={item.key} style={styles.tab} onPress={() => setTab(item.key)}>
                        <Text style={[styles.tabLabel, tab === item.key ? styles.tabActive : null]}>{item.label}</Text>
                    </TouchableOpacity>
                ))}
            </View>
        </View>
    );
}

function Gate() {
    const { session, loading } = useAuth();
    if (loading) {
        return (
            <View style={styles.splash}>
                <ActivityIndicator color={colors.accent} />
            </View>
        );
    }
    return session ? <Shell /> : <LoginScreen />;
}

export function App() {
    return (
        <AuthProvider store={nativeSecureStore}>
            <Gate />
        </AuthProvider>
    );
}

const styles = StyleSheet.create({
    shell: { flex: 1, backgroundColor: colors.bg, paddingTop: 44 },
    splash: { flex: 1, backgroundColor: colors.bg, alignItems: 'center', justifyContent: 'center' },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 20,
        paddingBottom: 8,
    },
    brand: { color: colors.accent, fontWeight: '700', letterSpacing: 1 },
    signOut: { color: colors.muted },
    content: { flex: 1 },
    tabBar: { flexDirection: 'row', borderTopColor: colors.border, borderTopWidth: 1, backgroundColor: colors.card },
    tab: { flex: 1, alignItems: 'center', paddingVertical: 12 },
    tabLabel: { color: colors.muted, fontSize: 12 },
    tabActive: { color: colors.accent, fontWeight: '700' },
});
