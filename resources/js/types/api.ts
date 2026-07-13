// Types mirror the Laravel API Resources in app/Modules/**/Interfaces/Http/Resources.
// Kept hand-authored (rather than generated) so they track the `data` envelopes
// the API actually returns and stay readable in review.

export interface Me {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
}

export interface Student {
    id: number;
    code: string;
    first_name: string;
    last_name: string;
    full_name: string;
    date_of_birth: string | null;
    enrollment_status: string | null;
    class_section_id: number | null;
    has_login: boolean;
    created_at: string | null;
    updated_at: string | null;
    class_section?: { id: number; grade: string; section: string };
}

export interface WalletAccount {
    id: number;
    owner_type: string;
    owner_id: number;
    balance_minor: number;
    currency: string;
    created_at: string | null;
}

export interface WalletTransaction {
    id: number;
    account_id: number;
    type: 'credit' | 'debit';
    amount_minor: number;
    balance_after_minor: number;
    reference_type: string | null;
    reference_id: number | null;
    created_at: string | null;
}

export interface WalletSnapshot {
    accounts: WalletAccount[];
    transactions: WalletTransaction[];
}

export interface AppNotification {
    id: string;
    type: string;
    data: Record<string, unknown> & { message?: string };
    read_at: string | null;
    created_at: string | null;
}

export interface AuditLogEntry {
    id: number;
    user_id: number | null;
    action: string;
    method: string;
    path: string;
    status: number;
    ip_address: string | null;
    created_at: string | null;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

export interface Paginated<T> {
    items: T[];
    meta: PaginationMeta | null;
}
