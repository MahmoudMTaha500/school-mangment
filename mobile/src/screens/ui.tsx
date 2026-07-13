import React from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from 'react-native';

export const colors = {
    bg: '#020617',
    card: '#0f172a',
    border: '#1e293b',
    text: '#f8fafc',
    muted: '#94a3b8',
    accent: '#38bdf8',
    danger: '#fda4af',
};

export function Screen({ title, subtitle, children }: { title: string; subtitle?: string; children: React.ReactNode }) {
    return (
        <ScrollView style={styles.screen} contentContainerStyle={styles.content}>
            <Text style={styles.title}>{title}</Text>
            {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
            <View style={styles.body}>{children}</View>
        </ScrollView>
    );
}

export function Card({ children }: { children: React.ReactNode }) {
    return <View style={styles.card}>{children}</View>;
}

export function AsyncBody({
    loading,
    error,
    empty,
    children,
}: {
    loading: boolean;
    error: string | null;
    empty?: boolean;
    children: React.ReactNode;
}) {
    if (loading) return <ActivityIndicator color={colors.accent} style={{ marginTop: 24 }} />;
    if (error) return <Text style={styles.error}>{error}</Text>;
    if (empty) return <Text style={styles.muted}>Nothing here yet.</Text>;
    return <>{children}</>;
}

export function Muted({ children }: { children: React.ReactNode }) {
    return <Text style={styles.muted}>{children}</Text>;
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.bg },
    content: { padding: 20, paddingBottom: 40 },
    title: { color: colors.text, fontSize: 24, fontWeight: '600' },
    subtitle: { color: colors.muted, fontSize: 14, marginTop: 4 },
    body: { marginTop: 16, gap: 12 },
    card: { backgroundColor: colors.card, borderColor: colors.border, borderWidth: 1, borderRadius: 16, padding: 16 },
    error: { color: colors.danger, marginTop: 16 },
    muted: { color: colors.muted, marginTop: 8 },
});
