import type { ReactNode } from 'react';
import { Icon, type IconName } from '@/components/Icon';
import { Alert } from '@/components/ui/Alert';
import { Loader } from '@/components/ui/Loader';

interface PageHeaderProps { eyebrow: string; title: string; description: string; icon: IconName; action?: ReactNode; }

export function PageHeader({ eyebrow, title, description, icon, action }: PageHeaderProps) {
    return (
        <header className="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 pt-2 animate-in">
            <div className="flex items-center gap-5">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-violet-500/20 bg-violet-500/10 text-violet-400 shadow-[0_0_30px_rgba(139,92,246,0.15)]">
                    <Icon name={icon} className="h-6 w-6" />
                </div>
                <div>
                    <span className="block text-[10px] font-bold uppercase tracking-widest text-slate-500">{eyebrow}</span>
                    <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">{title}</h1>
                    <p className="mt-1 text-sm text-slate-400">{description}</p>
                </div>
            </div>
            {action && <div>{action}</div>}
        </header>
    );
}

export function LoadingState({ label = 'Loading your data…' }: { label?: string }) {
    return (
        <div className="flex flex-col items-center justify-center gap-4 p-12 text-slate-400">
            <Loader size="lg" variant="pulse" />
            <p className="text-sm font-medium">{label}</p>
        </div>
    );
}

export function EmptyState({ icon, title, description }: { icon: IconName; title: string; description: string }) {
    return (
        <div className="flex flex-col items-center justify-center p-12 text-center text-slate-400">
            <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800/50 text-slate-500 shadow-inner border border-white/5">
                <Icon name={icon} className="h-6 w-6" />
            </div>
            <h3 className="text-base font-semibold text-slate-200">{title}</h3>
            <p className="mt-1 max-w-sm text-sm">{description}</p>
        </div>
    );
}

export function ErrorState({ message }: { message: string }) {
    return (
        <div className="my-4">
            <Alert variant="error" title="Something went wrong">
                {message}
            </Alert>
        </div>
    );
}
