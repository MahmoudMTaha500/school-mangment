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
    relationship?: string;
    is_primary_contact?: boolean;
    created_at: string | null;
    updated_at: string | null;
    class_section?: { id: number; grade: string; section: string };
}

export interface ClassSection {
    id: number;
    academic_year_id: number;
    grade: string;
    section: string;
    label: string;
    status: 'active' | 'archived';
    students_count?: number;
}

export interface ParentProfile {
    id: number;
    user_id: number;
    name: string;
    email: string;
    status: 'active' | 'archived';
    archived_at: string | null;
    students: Student[];
    created_at: string | null;
    updated_at: string | null;
}

export interface HomeworkAssignment {
    teacher_id: number;
    teacher_name: string;
    class_section_id: number;
    class_label: string;
    subject_id: number;
    subject_name: string;
}

export interface HomeworkRubricCriterion {
    id: number;
    title: string;
    max_score: number;
    position: number;
}

export interface Homework {
    id: number;
    class_section_id: number;
    subject_id: number;
    teacher_id: number;
    title: string;
    body: string;
    due_at: string;
    status: 'assigned' | 'archived';
    archived_at: string | null;
    rubric_criteria: HomeworkRubricCriterion[];
    submissions_count?: number;
    created_at: string | null;
}

export interface HomeworkSubmission {
    id: number;
    homework_id: number;
    student_id: number;
    body: string;
    submitted_at: string;
    status: 'submitted' | 'late' | 'graded';
    is_late: boolean;
    grade: number | null;
    feedback: string | null;
    rubric_scores: Array<{ criterion_id: number; score: number }>;
    graded_by: number | null;
    graded_at: string | null;
    updated_at: string | null;
}

export interface WalletAccount {
    id: number;
    owner_type: string;
    owner_id: number;
    balance_minor: number;
    currency: string;
    status: 'active' | 'archived';
    archived_at: string | null;
    created_at: string | null;
    updated_at: string | null;
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
