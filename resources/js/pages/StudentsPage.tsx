import { useState, type FormEvent } from 'react';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { ApiError } from '@/lib/apiClient';
import type { Paginated, Student } from '@/types/api';
import { EmptyState, ErrorState, LoadingState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/Card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/Table';
import { Alert } from '@/components/ui/Alert';
import { Loader } from '@/components/ui/Loader';

export function StudentsPage() {
    const { api } = useAuth();
    const students = useAsync<Paginated<Student>>(() => api.getPaginated<Student>('/sis/students'), []);
    const [form, setForm] = useState({ code: '', first_name: '', last_name: '' });
    const [error, setError] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);

    async function onCreate(event: FormEvent) {
        event.preventDefault(); setError(null); setSaving(true);
        try { await api.post('/sis/students', form); setForm({ code: '', first_name: '', last_name: '' }); students.reload(); }
        catch (err) { setError(err instanceof ApiError ? err.message : 'Could not create student.'); }
        finally { setSaving(false); }
    }

    return <div className="inner-page">
        <PageHeader eyebrow="School directory" title="Students" description="Manage enrollment and keep student information organized." icon="students" />
        <div className="grid gap-6">
            <Card variant="default" className="animate-in delay-one">
                <CardHeader>
                    <div>
                        <span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">New enrollment</span>
                        <CardTitle>Add a student</CardTitle>
                    </div>
                    <div className="flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/[0.02] px-2.5 py-1.5 text-[10px] font-medium text-slate-400"><Icon name="plus" className="h-3 w-3" /> New record</div>
                </CardHeader>
                <CardContent>
                    <form onSubmit={onCreate} className="grid grid-cols-1 md:grid-cols-3 gap-4" aria-label="Add student">
                        <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Student code</span><input className="field" placeholder="e.g. ST-1042" value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} required /></label>
                        <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">First name</span><input className="field" placeholder="First name" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required /></label>
                        <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Last name</span><input className="field" placeholder="Last name" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} required /></label>
                        <button className="primary col-span-1 md:col-span-3 mt-2 flex items-center justify-center gap-2" disabled={saving}>
                            {saving ? <Loader size="sm" variant="spinner" className="text-white border-t-white" /> : <Icon name="plus" className="h-4 w-4" />}
                            {saving ? 'Saving…' : 'Add student'}
                        </button>
                    </form>
                    {error && <Alert variant="error" className="mt-4" title="Enrollment failed">{error}</Alert>}
                </CardContent>
            </Card>

            <Card variant="default" className="animate-in delay-two">
                <CardHeader>
                    <div>
                        <span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">All records</span>
                        <CardTitle>Student directory</CardTitle>
                    </div>
                    <strong className="flex items-center gap-1.5 rounded-lg border border-violet-500/20 bg-violet-500/10 px-2.5 py-1.5 text-[10px] font-bold text-violet-400">{students.data?.meta?.total ?? 0} students</strong>
                </CardHeader>
                <CardContent className="p-0">
                    {students.loading && <div className="p-16"><Loader size="lg" variant="pulse" className="mx-auto" /></div>}
                    {students.error && <div className="p-6"><Alert variant="error" title="Directory Error">{students.error}</Alert></div>}
                    {students.data && students.data.items.length === 0 && !students.loading && <div className="p-6"><EmptyState icon="students" title="No students yet" description="Add your first student using the enrollment form above." /></div>}
                    {students.data && students.data.items.length > 0 && (
                        <Table className="border-0 rounded-none bg-transparent">
                            <TableHeader className="bg-slate-900/40">
                                <TableRow className="hover:bg-transparent">
                                    <TableHead>Student</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Class Section</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {students.data.items.map((student) => (
                                    <TableRow key={student.id}>
                                        <TableCell className="flex items-center gap-3 py-3">
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 text-xs font-bold uppercase text-violet-400 shadow-inner">
                                                {student.first_name.charAt(0)}{student.last_name.charAt(0)}
                                            </div>
                                            <div>
                                                <strong className="block text-sm font-semibold text-slate-200">{student.full_name}</strong>
                                                <span className="block mt-0.5 text-[11px] text-slate-500">{student.code}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-medium text-emerald-400"><span className="h-1.5 w-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_theme(colors.emerald.500)]" /> {student.enrollment_status || 'Enrolled'}</span>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <span className="block text-sm text-slate-300">{student.class_section ? `${student.class_section.grade} · ${student.class_section.section}` : 'Unassigned'}</span>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </CardContent>
            </Card>
        </div>
    </div>;
}
