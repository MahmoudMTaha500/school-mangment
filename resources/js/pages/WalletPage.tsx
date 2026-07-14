import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { formatDate, formatMinor } from '@/lib/format';
import type { WalletSnapshot } from '@/types/api';
import { EmptyState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/Table';
import { Loader } from '@/components/ui/Loader';
import { Alert } from '@/components/ui/Alert';

export function WalletPage() {
    const { api, can } = useAuth();
    const endpoint = can('wallet.manage') ? '/wallet/overview' : '/wallet/me';
    const wallet = useAsync<WalletSnapshot>(() => api.get<WalletSnapshot>(endpoint), [api, endpoint]);
    return <div className="inner-page">
        <PageHeader eyebrow="Finance center" title="Wallet" description="Monitor balances and review recent financial activity." icon="wallet" />
        {wallet.loading && <div className="p-16"><Loader size="lg" variant="pulse" className="mx-auto" /></div>}
        {wallet.error && <div className="mb-6"><Alert variant="error" title="Could not load wallet">{wallet.error}</Alert></div>}
        
        {wallet.data && (
            <div className="grid gap-6">
                <section className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 animate-in delay-one">
                    {wallet.data.accounts.map((account, i) => (
                        <Card key={account.id} variant="gradient" className={`relative overflow-hidden ${i % 2 === 0 ? 'from-violet-900/40 to-slate-900' : 'from-emerald-900/30 to-slate-900'}`}>
                            <div className="absolute -right-8 -bottom-10 h-32 w-32 rounded-full border border-white/5 bg-white/[0.02]" />
                            <CardContent className="flex flex-col h-full min-h-[190px] p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex h-10 w-12 items-center justify-center rounded-lg bg-white/10 text-white/80 backdrop-blur-sm">
                                        <Icon name="creditCard" className="h-5 w-5" />
                                    </div>
                                    <small className="text-[9px] tracking-[0.15em] text-slate-400">•••• {String(account.id).padStart(4, '0')}</small>
                                </div>
                                <div className="mt-auto pt-6">
                                    <span className="block text-[9px] uppercase tracking-widest text-slate-400">Available balance</span>
                                    <strong className="block mt-1 text-3xl font-bold tracking-tight text-white">{formatMinor(account.balance_minor, account.currency)}</strong>
                                </div>
                                <div className="mt-4 flex items-center justify-between text-[9px] uppercase tracking-wider text-slate-500">
                                    <span>{account.owner_type.split('\\').pop()} #{account.owner_id}</span>
                                    <strong className="text-slate-400">{account.currency}</strong>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <Card variant="default" className="animate-in delay-two">
                    <CardHeader>
                        <div>
                            <span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Ledger</span>
                            <CardTitle>Recent transactions</CardTitle>
                        </div>
                        <div className="flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/[0.02] px-2.5 py-1.5 text-[10px] font-medium text-slate-400"><Icon name="clock" className="h-3 w-3" /> Latest activity</div>
                    </CardHeader>
                    <CardContent className="p-0">
                        {wallet.data.transactions.length === 0 ? (
                            <div className="p-6"><EmptyState icon="wallet" title="No transactions yet" description="New wallet activity will appear here." /></div>
                        ) : (
                            <Table className="border-0 rounded-none bg-transparent">
                                <TableHeader className="bg-slate-900/40">
                                    <TableRow className="hover:bg-transparent">
                                        <TableHead>Type</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Balance after</TableHead>
                                        <TableHead>When</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {wallet.data.transactions.map((tx) => (
                                        <TableRow key={tx.id}>
                                            <TableCell>
                                                <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-medium capitalize ${tx.type === 'credit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'}`}>
                                                    <Icon name={tx.type === 'credit' ? 'arrow' : 'clock'} className="h-3 w-3" /> {tx.type}
                                                </span>
                                            </TableCell>
                                            <TableCell className="font-semibold text-slate-200">{formatMinor(tx.amount_minor, 'USD')}</TableCell>
                                            <TableCell className="text-slate-400">{formatMinor(tx.balance_after_minor, 'USD')}</TableCell>
                                            <TableCell className="text-[11px] text-slate-500">{formatDate(tx.created_at)}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        )}
    </div>;
}
