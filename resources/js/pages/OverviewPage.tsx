import { useAuth } from '@/auth/AuthContext';
import { visibleNavItems } from '@/navigation';
import { Link } from 'react-router-dom';
import { Icon, type IconName } from '@/components/Icon';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';

const shortcutDetails: Record<string, { description: string; icon: IconName; tone: string }> = {
    '/students': { description: 'Manage records and enrollment', icon: 'students', tone: 'violet' },
    '/wallet': { description: 'Accounts and transactions', icon: 'wallet', tone: 'mint' },
    '/reports': { description: 'Explore school insights', icon: 'reports', tone: 'amber' },
    '/notifications': { description: 'Messages and updates', icon: 'notifications', tone: 'blue' },
    '/audit-logs': { description: 'Review workspace activity', icon: 'audit', tone: 'rose' },
};

export function OverviewPage() {
    const { me, can } = useAuth();
    const shortcuts = visibleNavItems(can).filter((item) => item.path !== '/');

    return (
        <div className="dashboard-page">
            <section className="welcome-hero animate-in">
                <div className="hero-copy">
                    <span className="hero-kicker"><Icon name="sparkles" /> Your school at a glance</span>
                    <h1>Good day, {me?.name?.split(' ')[0] || 'there'}.</h1>
                    <p>Everything is running smoothly. Here is your workspace overview for today.</p>
                    <div className="hero-actions">
                        {shortcuts[0] && <Link to={shortcuts[0].path} className="hero-primary">Open {shortcuts[0].label}<Icon name="arrow" /></Link>}
                        <Link to="/notifications" className="hero-secondary">View updates</Link>
                    </div>
                </div>
                <div className="hero-orbit" aria-hidden="true">
                    <div className="orbit-ring orbit-ring-one" />
                    <div className="orbit-ring orbit-ring-two" />
                    <div className="hero-badge"><Icon name="shield" /><span><strong>All systems</strong><small>Operational</small></span></div>
                    <span className="orbit-dot dot-one" /><span className="orbit-dot dot-two" /><span className="orbit-dot dot-three" />
                </div>
            </section>

            <section className="grid grid-cols-1 md:grid-cols-3 gap-4 animate-in delay-one" aria-label="Workspace summary">
                <Card variant="glass" className="flex items-center gap-4 p-5 hover:-translate-y-1 transition-transform">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/15 text-violet-400">
                        <Icon name="overview" className="h-5 w-5" />
                    </div>
                    <div>
                        <div className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Available modules</div>
                        <div className="text-2xl font-bold text-white leading-tight">{shortcuts.length}</div>
                        <div className="text-[10px] text-slate-500">Ready to use</div>
                    </div>
                </Card>
                <Card variant="glass" className="flex items-center gap-4 p-5 hover:-translate-y-1 transition-transform">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400">
                        <Icon name="shield" className="h-5 w-5" />
                    </div>
                    <div>
                        <div className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Your permissions</div>
                        <div className="text-2xl font-bold text-white leading-tight">{me?.permissions.length ?? 0}</div>
                        <div className="text-[10px] text-slate-500">Access enabled</div>
                    </div>
                </Card>
                <Card variant="glass" className="flex items-center gap-4 p-5 hover:-translate-y-1 transition-transform">
                    <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400">
                        <Icon name="notifications" className="h-5 w-5" />
                    </div>
                    <div>
                        <div className="text-[10px] font-bold uppercase tracking-widest text-slate-400">Account status</div>
                        <div className="text-xl font-bold text-emerald-400 leading-tight">Active</div>
                        <div className="text-[10px] flex items-center gap-1.5 text-slate-500">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_theme(colors.emerald.500)] animate-pulse" /> Secure session
                        </div>
                    </div>
                </Card>
            </section>

            <div className="grid grid-cols-1 lg:grid-cols-[1.8fr_0.7fr] gap-4 animate-in delay-two">
                <Card variant="default">
                    <CardHeader>
                        <div>
                            <span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Workspace</span>
                            <CardTitle>Quick access</CardTitle>
                        </div>
                        <span className="text-xs text-slate-500">{shortcuts.length} modules</span>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {shortcuts.map((item) => (
                                <Link key={item.path} to={item.path} className="flex items-center gap-4 rounded-xl border border-white/5 bg-white/[0.02] p-4 transition-all hover:-translate-y-0.5 hover:border-violet-500/20 hover:bg-white/[0.04]">
                                    <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${shortcutDetails[item.path]?.tone === 'violet' ? 'bg-violet-500/10 text-violet-400' : shortcutDetails[item.path]?.tone === 'mint' ? 'bg-emerald-500/10 text-emerald-400' : shortcutDetails[item.path]?.tone === 'amber' ? 'bg-amber-500/10 text-amber-400' : shortcutDetails[item.path]?.tone === 'rose' ? 'bg-rose-500/10 text-rose-400' : 'bg-blue-500/10 text-blue-400'}`}>
                                        <Icon name={shortcutDetails[item.path]?.icon || 'overview'} className="h-4 w-4" />
                                    </span>
                                    <span className="flex-1 min-w-0">
                                        <strong className="block text-sm font-semibold text-slate-200">{item.label}</strong>
                                        <small className="block truncate text-[10px] text-slate-500">{shortcutDetails[item.path]?.description || `Open ${item.label.toLowerCase()}`}</small>
                                    </span>
                                    <Icon name="arrow" className="h-4 w-4 text-slate-600 transition-colors group-hover:text-violet-400" />
                                </Link>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card variant="gradient" className="text-center">
                    <CardHeader className="text-left border-white/5">
                        <div>
                            <span className="text-[9px] font-bold uppercase tracking-widest text-slate-400">Profile</span>
                            <CardTitle>Your access</CardTitle>
                        </div>
                        <Icon name="shield" className="h-5 w-5 text-violet-400" />
                    </CardHeader>
                    <CardContent className="flex flex-col items-center">
                        <div className="relative my-4 flex h-32 w-32 items-center justify-center rounded-full shadow-[0_0_40px_rgba(139,92,246,0.15)]" style={{ background: `conic-gradient(#8b5cf6 ${Math.min(100, (me?.permissions.length ?? 0) * 6)}%, rgba(255,255,255,0.05) 0)` }}>
                            <div className="absolute inset-2 rounded-full bg-slate-950"></div>
                            <div className="relative text-center">
                                <strong className="block text-3xl font-bold text-white">{me?.permissions.length ?? 0}</strong>
                                <span className="block text-[9px] uppercase tracking-widest text-slate-500">permissions</span>
                            </div>
                        </div>
                        <p className="max-w-[240px] text-xs leading-relaxed text-slate-400">
                            Your <strong className="font-medium text-violet-300 capitalize">{me?.roles.join(', ') || 'member'}</strong> role controls the tools and school data available to you.
                        </p>
                        <div className="mt-6 flex w-full items-center justify-between border-t border-white/10 pt-4 text-[10px]">
                            <span className="flex items-center gap-1.5 text-slate-400"><span className="h-1.5 w-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_theme(colors.emerald.500)]" /> Protected session</span>
                            <strong className="font-semibold text-emerald-400">Live</strong>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
