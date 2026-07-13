
import { StyleSheet, Text, View } from 'react-native';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/lib/useAsync';
import { formatDate, formatMinor } from '@/lib/format';
import type { WalletSnapshot } from '@/lib/types';
import { AsyncBody, Card, colors, Screen } from './ui';

export function WalletScreen() {
    const { api } = useAuth();
    const wallet = useAsync<WalletSnapshot>(() => api.get<WalletSnapshot>('/wallet/me'), []);

    return (
        <Screen title="Wallet" subtitle="Balances and recent activity">
            <AsyncBody loading={wallet.loading} error={wallet.error}>
                {wallet.data?.accounts.map((account) => (
                    <Card key={account.id}>
                        <Text style={styles.muted}>
                            {account.owner_type} #{account.owner_id}
                        </Text>
                        <Text style={styles.balance}>{formatMinor(account.balance_minor, account.currency)}</Text>
                    </Card>
                ))}
                <Card>
                    <Text style={styles.cardTitle}>Recent transactions</Text>
                    {wallet.data?.transactions.length ? (
                        wallet.data.transactions.map((tx) => (
                            <View key={tx.id} style={styles.row}>
                                <Text style={styles.rowType}>{tx.type}</Text>
                                <Text style={styles.rowAmount}>{formatMinor(tx.amount_minor, 'USD')}</Text>
                                <Text style={styles.rowDate}>{formatDate(tx.created_at)}</Text>
                            </View>
                        ))
                    ) : (
                        <Text style={styles.muted}>No transactions yet.</Text>
                    )}
                </Card>
            </AsyncBody>
        </Screen>
    );
}

const styles = StyleSheet.create({
    muted: { color: colors.muted, fontSize: 13 },
    balance: { color: colors.text, fontSize: 24, fontWeight: '700', marginTop: 4 },
    cardTitle: { color: colors.text, fontSize: 16, fontWeight: '600', marginBottom: 8 },
    row: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6, borderTopColor: colors.border, borderTopWidth: 1 },
    rowType: { color: colors.text, textTransform: 'capitalize', flex: 1 },
    rowAmount: { color: colors.text, flex: 1, textAlign: 'center' },
    rowDate: { color: colors.muted, flex: 1, textAlign: 'right' },
});
