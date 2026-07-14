import { useMemo, useState, type FormEvent, type ReactNode } from 'react';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { ApiError } from '@/lib/apiClient';
import type { Paginated, ParentProfile, Student } from '@/types/api';
import { EmptyState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Alert } from '@/components/ui/Alert';
import { Loader } from '@/components/ui/Loader';

const emptyParent = { name: '', email: '', password: '' };

export function ParentsPage() {
    const { api } = useAuth();
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [form, setForm] = useState(emptyParent);
    const [editing, setEditing] = useState<ParentProfile | null>(null);
    const [linking, setLinking] = useState<ParentProfile | null>(null);
    const [link, setLink] = useState({ student_id: '', relationship: 'Parent', is_primary: true });
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const query = useMemo(() => {
        const params = new URLSearchParams({ per_page: '100' });
        if (search.trim()) params.set('search', search.trim());
        if (status) params.set('status', status);
        return params.toString();
    }, [search, status]);
    const parents = useAsync<Paginated<ParentProfile>>(() => api.getPaginated<ParentProfile>(`/sis/parents?${query}`), [query]);
    const students = useAsync<Paginated<Student>>(() => api.getPaginated<Student>('/sis/students?status=active&per_page=100'), []);

    function reset() { setEditing(null); setForm(emptyParent); setError(null); }
    function beginEdit(parent: ParentProfile) { setEditing(parent); setForm({ name: parent.name, email: parent.email, password: '' }); setError(null); setNotice(null); window.scrollTo({ top: 0, behavior: 'smooth' }); }

    async function save(event: FormEvent) {
        event.preventDefault(); setSaving(true); setError(null); setNotice(null);
        try {
            if (editing) await api.patch(`/sis/parents/${editing.id}`, { name: form.name, email: form.email });
            else await api.post('/sis/parents', form);
            setNotice(editing ? 'Parent profile updated.' : 'Parent account created.'); reset(); parents.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not save the parent.'); }
        finally { setSaving(false); }
    }

    async function archive(parent: ParentProfile) {
        if (!window.confirm(`Archive ${parent.name}? Existing family links and history will remain.`)) return;
        try { await api.delete(`/sis/parents/${parent.id}`); setNotice('Parent archived.'); parents.reload(); }
        catch (err) { setError(err instanceof ApiError ? err.message : 'Could not archive the parent.'); }
    }

    async function linkStudent(event: FormEvent) {
        event.preventDefault(); if (!linking || !link.student_id) return;
        setSaving(true); setError(null);
        try {
            await api.post(`/sis/parents/${linking.id}/students/${link.student_id}`, { relationship: link.relationship, is_primary: link.is_primary });
            setNotice(`${linking.name} is now linked to the student.`); setLinking(null); setLink({ student_id: '', relationship: 'Parent', is_primary: true }); parents.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not link this family.'); }
        finally { setSaving(false); }
    }

    const records = parents.data?.items ?? [];
    const linkedChildren = records.reduce((total, parent) => total + parent.students.length, 0);
    return <div className="inner-page">
        <PageHeader eyebrow="Family directory" title="Parent management" description="Maintain guardian access and connect each family to the right student records." icon="parents" />
        <div className="stats-grid animate-in delay-one">
            <div className="stat-card"><span className="stat-icon violet"><Icon name="parents" /></span><div><small>Parent profiles</small><strong>{records.length}</strong><em>visible</em></div></div>
            <div className="stat-card"><span className="stat-icon mint"><Icon name="check" /></span><div><small>Active accounts</small><strong>{records.filter((item) => item.status === 'active').length}</strong><em>active</em></div></div>
            <div className="stat-card"><span className="stat-icon blue"><Icon name="students" /></span><div><small>Family links</small><strong>{linkedChildren}</strong><em>children</em></div></div>
        </div>

        <Card className="animate-in delay-one"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Account management</span><CardTitle>{editing ? 'Edit parent profile' : 'Create parent account'}</CardTitle></div>{editing && <button className="secondary" onClick={reset}>Cancel edit</button>}</CardHeader><CardContent>
            <form onSubmit={save} className="grid gap-4 md:grid-cols-3">
                <Field label="Full name"><input className="field" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></Field>
                <Field label="Email address"><input type="email" className="field" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required /></Field>
                {!editing && <Field label="Temporary password"><input type="password" minLength={12} className="field" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required /></Field>}
                <button className="primary flex items-center justify-center gap-2 md:col-span-3" disabled={saving}>{saving ? <Loader size="sm" variant="spinner" /> : <Icon name={editing ? 'check' : 'plus'} className="h-4 w-4" />}{editing ? 'Save profile' : 'Create parent account'}</button>
            </form>
            {error && <Alert className="mt-4" variant="error" title="Action failed">{error}</Alert>}{notice && <Alert className="mt-4" variant="success" title="Done">{notice}</Alert>}
        </CardContent></Card>

        {linking && <Card className="animate-in"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Family relationship</span><CardTitle>Link a student to {linking.name}</CardTitle></div><button className="secondary" onClick={() => setLinking(null)}>Cancel</button></CardHeader><CardContent><form onSubmit={linkStudent} className="grid gap-4 md:grid-cols-3"><Field label="Student"><select className="field" value={link.student_id} onChange={(e) => setLink({ ...link, student_id: e.target.value })} required><option value="">Select student</option>{students.data?.items.map((student) => <option key={student.id} value={student.id}>{student.full_name} · {student.code}</option>)}</select></Field><Field label="Relationship"><input className="field" value={link.relationship} onChange={(e) => setLink({ ...link, relationship: e.target.value })} placeholder="Mother, Father, Guardian..." required /></Field><label className="flex items-center gap-3 self-end rounded-xl border border-slate-700 px-4 py-3 text-sm text-slate-300"><input type="checkbox" checked={link.is_primary} onChange={(e) => setLink({ ...link, is_primary: e.target.checked })} /> Primary contact</label><button className="primary md:col-span-3" disabled={saving}>Save family link</button></form></CardContent></Card>}

        <Card className="animate-in delay-two"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Family records</span><CardTitle>Parent directory</CardTitle></div><strong className="count-badge">{parents.data?.meta?.total ?? 0} profiles</strong></CardHeader><CardContent>
            <div className="grid gap-3 border-b border-white/5 pb-5 md:grid-cols-2"><input className="field" placeholder="Search name or email..." value={search} onChange={(e) => setSearch(e.target.value)} /><select className="field" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">All statuses</option><option value="active">Active</option><option value="archived">Archived</option></select></div>
            {parents.loading && <div className="p-12"><Loader size="lg" variant="pulse" className="mx-auto" /></div>}{parents.error && <Alert className="mt-4" variant="error">{parents.error}</Alert>}
            {!parents.loading && records.length === 0 && <EmptyState icon="parents" title="No matching parents" description="Create an account or change the filters." />}
            <div className="divide-y divide-white/5">{records.map((parent) => <div key={parent.id} className="grid gap-4 py-5 lg:grid-cols-[1fr_1.4fr_auto] lg:items-center"><div><strong className="block text-sm text-slate-200">{parent.name}</strong><span className="text-[11px] text-slate-500">{parent.email}</span><span className="status-pill mt-2 inline-flex">{parent.status}</span></div><div><span className="mb-2 block text-[9px] font-bold uppercase tracking-widest text-slate-500">Linked students</span><div className="flex flex-wrap gap-2">{parent.students.length ? parent.students.map((student) => <span key={student.id} className="rounded-lg border border-white/5 bg-white/[0.03] px-3 py-2 text-[11px] text-slate-300">{student.full_name}<small className="ml-1 text-slate-500">· {student.relationship ?? 'Family'}{student.is_primary_contact ? ' · Primary' : ''}</small></span>) : <span className="text-xs text-amber-400">No student linked yet</span>}</div></div><div className="flex flex-wrap justify-end gap-2"><button className="secondary px-3 py-2" onClick={() => setLinking(parent)}>Link student</button><button className="secondary px-3 py-2" onClick={() => beginEdit(parent)}>Edit</button>{parent.status !== 'archived' && <button className="secondary px-3 py-2 text-rose-400" onClick={() => archive(parent)}>Archive</button>}</div></div>)}</div>
        </CardContent></Card>
    </div>;
}

function Field({ label, children }: { label: string; children: ReactNode }) { return <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold uppercase tracking-wide text-slate-400">{label}</span>{children}</label>; }
