import { HTMLAttributes, ReactNode } from 'react';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
    children: ReactNode;
    className?: string;
    variant?: 'default' | 'glass' | 'gradient';
}

export function Card({ children, className = '', variant = 'default', ...props }: CardProps) {
    const baseClasses = 'relative overflow-hidden rounded-2xl border transition-all duration-300';
    
    const variants = {
        default: 'border-slate-800/60 bg-slate-900/60 shadow-xl backdrop-blur-md',
        glass: 'border-white/10 bg-white/5 shadow-2xl backdrop-blur-xl hover:bg-white/10 hover:border-white/20',
        gradient: 'border-white/5 bg-gradient-to-br from-slate-900/90 to-slate-950/90 shadow-2xl backdrop-blur-lg',
    };

    return (
        <div className={`${baseClasses} ${variants[variant]} ${className}`} {...props}>
            {children}
        </div>
    );
}

export function CardHeader({ children, className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={`flex items-center justify-between px-6 py-5 border-b border-white/5 ${className}`} {...props}>
            {children}
        </div>
    );
}

export function CardTitle({ children, className = '', ...props }: HTMLAttributes<HTMLHeadingElement>) {
    return (
        <h2 className={`text-lg font-semibold tracking-tight text-white ${className}`} {...props}>
            {children}
        </h2>
    );
}

export function CardDescription({ children, className = '', ...props }: HTMLAttributes<HTMLParagraphElement>) {
    return (
        <p className={`text-sm text-slate-400 mt-1 ${className}`} {...props}>
            {children}
        </p>
    );
}

export function CardContent({ children, className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={`px-6 py-5 ${className}`} {...props}>
            {children}
        </div>
    );
}
