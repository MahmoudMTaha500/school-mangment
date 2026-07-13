import { HTMLAttributes, TdHTMLAttributes, ThHTMLAttributes } from 'react';

export function Table({ className = '', ...props }: HTMLAttributes<HTMLTableElement>) {
    return (
        <div className="w-full overflow-auto rounded-xl border border-slate-800/60 bg-slate-900/40 backdrop-blur-sm shadow-sm">
            <table className={`w-full text-left text-sm text-slate-300 ${className}`} {...props} />
        </div>
    );
}

export function TableHeader({ className = '', ...props }: HTMLAttributes<HTMLTableSectionElement>) {
    return <thead className={`border-b border-slate-800/60 bg-slate-900/80 text-xs font-semibold text-slate-400 uppercase tracking-wider ${className}`} {...props} />;
}

export function TableBody({ className = '', ...props }: HTMLAttributes<HTMLTableSectionElement>) {
    return <tbody className={`divide-y divide-slate-800/60 ${className}`} {...props} />;
}

export function TableRow({ className = '', ...props }: HTMLAttributes<HTMLTableRowElement>) {
    return (
        <tr 
            className={`transition-colors hover:bg-white/[0.02] data-[state=selected]:bg-slate-800 ${className}`} 
            {...props} 
        />
    );
}

export function TableHead({ className = '', ...props }: ThHTMLAttributes<HTMLTableCellElement>) {
    return (
        <th 
            className={`h-12 px-6 align-middle font-medium text-slate-400 [&:has([role=checkbox])]:pr-0 ${className}`} 
            {...props} 
        />
    );
}

export function TableCell({ className = '', ...props }: TdHTMLAttributes<HTMLTableCellElement>) {
    return (
        <td 
            className={`p-6 align-middle [&:has([role=checkbox])]:pr-0 ${className}`} 
            {...props} 
        />
    );
}
