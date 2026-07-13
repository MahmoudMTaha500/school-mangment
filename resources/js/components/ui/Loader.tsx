import { HTMLAttributes } from 'react';

interface LoaderProps extends HTMLAttributes<HTMLDivElement> {
    size?: 'sm' | 'md' | 'lg';
    variant?: 'spinner' | 'dots' | 'pulse';
    className?: string;
}

export function Loader({ size = 'md', variant = 'spinner', className = '', ...props }: LoaderProps) {
    const sizes = {
        sm: 'w-4 h-4',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
    };

    if (variant === 'pulse') {
        return (
            <div className={`relative flex h-full w-full items-center justify-center ${className}`} {...props}>
                <div className={`${sizes[size]} animate-ping rounded-full bg-violet-400 opacity-75`}></div>
                <div className={`absolute ${sizes[size]} rounded-full bg-violet-500`}></div>
            </div>
        );
    }

    if (variant === 'dots') {
        return (
            <div className={`flex items-center space-x-1.5 ${className}`} {...props}>
                <div className="w-2 h-2 bg-violet-500 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
                <div className="w-2 h-2 bg-violet-500 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
                <div className="w-2 h-2 bg-violet-500 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
            </div>
        );
    }

    return (
        <div className={`animate-spin rounded-full border-2 border-slate-700 border-t-violet-500 ${sizes[size]} ${className}`} {...props} />
    );
}

export function Skeleton({ className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div 
            className={`animate-pulse rounded-md bg-slate-800/50 backdrop-blur-sm ${className}`} 
            {...props} 
        />
    );
}
