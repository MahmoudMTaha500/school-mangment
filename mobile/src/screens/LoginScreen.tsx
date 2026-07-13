import { useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { ApiError } from '@/lib/apiClient';

const DEFAULT_BASE = 'https://green-valley.localhost/api/v1';

export function LoginScreen() {
    const { login } = useAuth();
    const [baseUrl, setBaseUrl] = useState(DEFAULT_BASE);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);

    async function onSubmit() {
        setError(null);
        setSubmitting(true);
        try {
            await login(baseUrl, email, password);
        } catch (err) {
            setError(err instanceof ApiError ? err.message : 'Sign in failed.');
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <View style={styles.container}>
            <Text style={styles.brand}>SCHOOL MANAGEMENT</Text>
            <Text style={styles.heading}>Sign in</Text>

            <Text style={styles.label}>School API URL</Text>
            <TextInput
                style={styles.input}
                value={baseUrl}
                onChangeText={setBaseUrl}
                autoCapitalize="none"
                autoCorrect={false}
            />
            <Text style={styles.label}>Email</Text>
            <TextInput
                style={styles.input}
                value={email}
                onChangeText={setEmail}
                autoCapitalize="none"
                keyboardType="email-address"
            />
            <Text style={styles.label}>Password</Text>
            <TextInput style={styles.input} value={password} onChangeText={setPassword} secureTextEntry />

            {error ? <Text style={styles.error}>{error}</Text> : null}

            <TouchableOpacity style={styles.button} onPress={onSubmit} disabled={submitting}>
                {submitting ? <ActivityIndicator color="#020617" /> : <Text style={styles.buttonText}>Sign in</Text>}
            </TouchableOpacity>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, justifyContent: 'center', padding: 24, backgroundColor: '#020617', gap: 8 },
    brand: { color: '#38bdf8', fontSize: 12, fontWeight: '600', letterSpacing: 2 },
    heading: { color: '#f8fafc', fontSize: 26, fontWeight: '600', marginBottom: 12 },
    label: { color: '#94a3b8', fontSize: 13, marginTop: 8 },
    input: {
        backgroundColor: '#0f172a',
        borderColor: '#334155',
        borderWidth: 1,
        borderRadius: 8,
        color: '#f8fafc',
        paddingHorizontal: 12,
        paddingVertical: 10,
    },
    error: { color: '#fda4af', marginTop: 8 },
    button: {
        backgroundColor: '#38bdf8',
        borderRadius: 8,
        paddingVertical: 12,
        alignItems: 'center',
        marginTop: 16,
    },
    buttonText: { color: '#020617', fontWeight: '700' },
});
