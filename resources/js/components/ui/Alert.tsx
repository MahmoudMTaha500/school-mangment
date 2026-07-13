import { HTMLAttributes, ReactNode } from 'react';
import { Icon, type IconName } from '@/components/Icon';

interface AlertProps extends HTMLAttributes<HTMLDivElement> {
    variant?: 'default' | 'success' | 'error' | 'warning';
    icon?: IconName | boolean;
    title?: string;
    children: ReactNode;
}

export function Alert({ className = '', variant = 'default', icon, title, children, ...props }: AlertProps) {
    const variants = {
        default: 'bg-slate-800/40 border-slate-700 text-slate-200',
        success: 'bg-emerald-950/30 border-emerald-900/50 text-emerald-200',
        error: 'bg-rose-950/30 border-rose-900/50 text-rose-200',
        warning: 'bg-amber-950/30 border-amber-900/50 text-amber-200',
    };

    const iconColors = {
        default: 'text-slate-400',
        success: 'text-emerald-400',
        error: 'text-rose-400',
        warning: 'text-amber-400',
    };

    let defaultIcon: IconName = 'sparkles';
    if (variant === 'success') defaultIcon = 'shield'; // Using existing icons based on typical sets, might need adjusting
    if (variant === 'error') defaultIcon = 'notifications';
    if (variant === 'warning') defaultIcon = 'notifications';

    const IconComponent = icon === false ? null : (
        <div className={`mt-0.5 shrink-0 ${iconColors[variant]}`}>
            <Icon name={typeof icon === 'string' ? icon : defaultIcon} className="w-5 h-5" />
        </div>
    );

    return (
        <div 
            role="alert"
            className={`relative flex w-full items-start gap-4 rounded-xl border p-4 backdrop-blur-sm transition-all ${variants[variant]} ${className}`}
            {...props}
        >
            {IconComponent}
            <div className="flex-1">
                {title && <h5 className="mb-1 font-medium leading-none tracking-tight">{title}</h5>}
                <div className="text-sm opacity-90 leading-relaxed">
                    {children}
                </div>
            </div>
        </div>
    );
}
