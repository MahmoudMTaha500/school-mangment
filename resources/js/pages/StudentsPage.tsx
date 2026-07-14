import { useMemo, useState, type FormEvent } from 'react';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { ApiError } from '@/lib/apiClient';
import type { ClassSection, Paginated, Student } from '@/types/api';
import { EmptyState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Alert } from '@/components/ui/Alert';
import { Loader } from '@/components/ui/Loader';

type StudentForm = { code: string; first_name: string; last_name: string; dob: string; class_section_id: string; enrollment_status: string };
const emptyForm: StudentForm = { code: '', first_name: '', last_name: '', dob: '', class_section_id: '', enrollment_status: 'active' };

export function StudentsPage() {
    const { api } = useAuth();
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('');
    const [classId, setClassId] = useState('');
    const [form, setForm] = useState<StudentForm>(emptyForm);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);
    const [accountStudent, setAccountStudent] = useState<Student | null>(null);
    const [account, setAccount] = useState({ email: '', password: '' });

    const query = useMemo(() => {
        const params = new URLSearchParams({ per_page: '100' });
        if (search.trim()) params.set('search', search.trim());
        if (status) params.set('status', status);
        if (classId) params.set('class_section_id', classId);
        return params.toString();
    }, [search, status, classId]);
    const students = useAsync<Paginated<Student>>(() => api.getPaginated<Student>(`/sis/students?${query}`), [query]);
    const sections = useAsync<Paginated<ClassSection>>(() => api.getPaginated<ClassSection>('/sis/class-sections?status=active&per_page=100'), []);

    function beginEdit(student: Student) {
        setEditingId(student.id);
        setForm({ code: student.code, first_name: student.first_name, last_name: student.last_name, dob: student.date_of_birth ?? '', class_section_id: student.class_section_id?.toString() ?? '', enrollment_status: student.enrollment_status ?? 'active' });
        setNotice(null); setError(null); window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() { setEditingId(null); setForm(emptyForm); setError(null); }

    async function saveStudent(event: FormEvent) {
        event.preventDefault(); setSaving(true); setError(null); setNotice(null);
        const payload = { ...form, dob: form.dob || null, class_section_id: form.class_section_id ? Number(form.class_section_id) : null };
        try {
            if (editingId) await api.patch(`/sis/students/${editingId}`, payload);
            else await api.post('/sis/students', payload);
            setNotice(editingId ? 'Student record updated.' : 'Student enrolled successfully.');
            resetForm(); students.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not save the student.'); }
        finally { setSaving(false); }
    }

    async function archive(student: Student) {
        if (!window.confirm(`Archive ${student.full_name}? Their history will be preserved.`)) return;
        setError(null);
        try { await api.delete(`/sis/students/${student.id}`); setNotice('Student archived.'); students.reload(); }
        catch (err) { setError(err instanceof ApiError ? err.message : 'Could not archive the student.'); }
    }

    async function createAccount(event: FormEvent) {
        event.preventDefault(); if (!accountStudent) return;
        setSaving(true); setError(null);
        try {
            await api.post(`/sis/students/${accountStudent.id}/account`, account);
            setNotice(`Login created for ${accountStudent.full_name}.`); setAccountStudent(null); setAccount({ email: '', password: '' }); students.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not create the login.'); }
        finally { setSaving(false); }
    }

    const records = students.data?.items ?? [];
    const active = records.filter((item) => item.enrollment_status === 'active').length;
    const unassigned = records.filter((item) => !item.class_section_id).length;

    return <div className="inner-page">
        <PageHeader eyebrow="Student information system" title="Student management" description="Enroll, place, update, archive, and provision student access from one workspace." icon="students" />
        <div className="stats-grid animate-in delay-one">
            <div className="stat-card"><span className="stat-icon violet"><Icon name="students" /></span><div><small>Visible records</small><strong>{records.length}</strong><em>students</em></div></div>
            <div className="stat-card"><span className="stat-icon mint"><Icon name="check" /></span><div><small>Active enrollment</small><strong>{active}</strong><em>active</em></div></div>
            <div className="stat-card"><span className="stat-icon amber"><Icon name="calendar" /></span><div><small>Needs placement</small><strong>{unassigned}</strong><em>unassigned</em></div></div>
        </div>

        <Card className="animate-in delay-one">
            <CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">{editingId ? 'Record maintenance' : 'New enrollment'}</span><CardTitle>{editingId ? 'Edit student' : 'Enroll a student'}</CardTitle></div>{editingId && <button className="secondary" onClick={resetForm}>Cancel edit</button>}</CardHeader>
            <CardContent>
                <form onSubmit={saveStudent} className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Field label="Student code"><input className="field" value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} placeholder="ST-1042" required /></Field>
                    <Field label="First name"><input className="field" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required /></Field>
                    <Field label="Last name"><input className="field" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} required /></Field>
                    <Field label="Date of birth"><input className="field" type="date" value={form.dob} onChange={(e) => setForm({ ...form, dob: e.target.value })} /></Field>
                    <Field label="Class placement"><select className="field" value={form.class_section_id} onChange={(e) => setForm({ ...form, class_section_id: e.target.value })}><option value="">Unassigned</option>{sections.data?.items.map((section) => <option key={section.id} value={section.id}>{section.label}</option>)}</select></Field>
                    <Field label="Enrollment status"><select className="field" value={form.enrollment_status} onChange={(e) => setForm({ ...form, enrollment_status: e.target.value })}><option value="active">Active</option><option value="inactive">Inactive</option><option value="graduated">Graduated</option></select></Field>
                    <button className="primary md:col-span-3 flex items-center justify-center gap-2" disabled={saving}>{saving ? <Loader size="sm" variant="spinner" /> : <Icon name={editingId ? 'check' : 'plus'} className="h-4 w-4" />}{saving ? 'Saving...' : editingId ? 'Save changes' : 'Enroll student'}</button>
                </form>
                {error && <Alert className="mt-4" variant="error" title="Action failed">{error}</Alert>}
                {notice && <Alert className="mt-4" variant="success" title="Done">{notice}</Alert>}
            </CardContent>
        </Card>

        {accountStudent && <Card className="animate-in"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Student portal</span><CardTitle>Create login for {accountStudent.full_name}</CardTitle></div><button className="secondary" onClick={() => setAccountStudent(null)}>Cancel</button></CardHeader><CardContent><form onSubmit={createAccount} className="grid gap-4 md:grid-cols-2"><Field label="Email"><input type="email" className="field" value={account.email} onChange={(e) => setAccount({ ...account, email: e.target.value })} required /></Field><Field label="Temporary password"><input type="password" minLength={12} className="field" value={account.password} onChange={(e) => setAccount({ ...account, password: e.target.value })} required /></Field><button className="primary md:col-span-2" disabled={saving}>Create student login</button></form></CardContent></Card>}

        <Card className="animate-in delay-two">
            <CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Directory</span><CardTitle>Student records</CardTitle></div><strong className="count-badge">{students.data?.meta?.total ?? 0} records</strong></CardHeader>
            <CardContent>
                <div className="grid gap-3 border-b border-white/5 pb-5 md:grid-cols-3"><input className="field" placeholder="Search name or code..." value={search} onChange={(e) => setSearch(e.target.value)} /><select className="field" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="graduated">Graduated</option><option value="archived">Archived</option></select><select className="field" value={classId} onChange={(e) => setClassId(e.target.value)}><option value="">All classes</option>{sections.data?.items.map((section) => <option key={section.id} value={section.id}>{section.label}</option>)}</select></div>
                {students.loading && <div className="p-12"><Loader size="lg" variant="pulse" className="mx-auto" /></div>}
                {students.error && <Alert className="mt-4" variant="error">{students.error}</Alert>}
                {!students.loading && records.length === 0 && <EmptyState icon="students" title="No matching students" description="Change the filters or enroll a new student." />}
                <div className="divide-y divide-white/5">{records.map((student) => <div key={student.id} className="grid items-center gap-4 py-4 md:grid-cols-[44px_1fr_1fr_auto]">
                    <div className="student-avatar">{student.first_name[0]}{student.last_name[0]}</div>
                    <div><strong className="block text-sm text-slate-200">{student.full_name}</strong><span className="text-[11px] text-slate-500">{student.code}{student.date_of_birth ? ` · Born ${student.date_of_birth}` : ''}</span></div>
                    <div><span className="block text-xs text-slate-300">{student.class_section ? `${student.class_section.grade} - ${student.class_section.section}` : 'Placement needed'}</span><span className="status-pill mt-1 inline-flex">{student.enrollment_status}</span></div>
                    <div className="flex flex-wrap justify-end gap-2"><button className="secondary px-3 py-2" onClick={() => beginEdit(student)}>Edit</button>{!student.has_login && student.enrollment_status !== 'archived' && <button className="secondary px-3 py-2" onClick={() => { setAccountStudent(student); setAccount({ email: '', password: '' }); }}>Create login</button>}{student.enrollment_status !== 'archived' && <button className="secondary px-3 py-2 text-rose-400" onClick={() => archive(student)}>Archive</button>}</div>
                </div>)}</div>
            </CardContent>
        </Card>
    </div>;
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
    return <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold uppercase tracking-wide text-slate-400">{label}</span>{children}</label>;
}
