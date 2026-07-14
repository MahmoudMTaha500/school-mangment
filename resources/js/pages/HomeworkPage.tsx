import { useMemo, useState, type FormEvent, type ReactNode } from 'react';
import { useAuth } from '@/auth/AuthContext';
import { useAsync } from '@/hooks/useAsync';
import { ApiError } from '@/lib/apiClient';
import type { Homework, HomeworkAssignment, HomeworkSubmission, Paginated } from '@/types/api';
import { EmptyState, PageHeader } from '@/components/PageHeader';
import { Icon } from '@/components/Icon';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Alert } from '@/components/ui/Alert';
import { Loader } from '@/components/ui/Loader';

type HomeworkForm = { assignment: string; title: string; body: string; due_at: string };
type RubricRow = { title: string; max_score: number };
type GradeDraft = { grade: string; feedback: string; scores: Record<number, string> };
const emptyForm: HomeworkForm = { assignment: '', title: '', body: '', due_at: '' };

export function HomeworkPage() {
    const { api, can } = useAuth();
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('assigned');
    const [assignmentFilter, setAssignmentFilter] = useState('');
    const [form, setForm] = useState<HomeworkForm>(emptyForm);
    const [editing, setEditing] = useState<Homework | null>(null);
    const [selected, setSelected] = useState<Homework | null>(null);
    const [rubric, setRubric] = useState<RubricRow[]>([{ title: '', max_score: 100 }]);
    const [gradeDrafts, setGradeDrafts] = useState<Record<number, GradeDraft>>({});
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    const options = useAsync<{ assignments: HomeworkAssignment[] }>(() => api.get('/homework-options'), []);
    const assignments = options.data?.assignments ?? [];
    const listQuery = useMemo(() => {
        const params = new URLSearchParams({ per_page: '100' });
        if (search.trim()) params.set('search', search.trim());
        if (status) params.set('status', status);
        if (assignmentFilter) {
            const assignment = assignments[Number(assignmentFilter)];
            if (assignment) { params.set('class_section_id', String(assignment.class_section_id)); params.set('subject_id', String(assignment.subject_id)); }
        }
        return params.toString();
    }, [search, status, assignmentFilter, assignments]);
    const homework = useAsync<Paginated<Homework>>(() => api.getPaginated<Homework>(`/homework?${listQuery}`), [listQuery]);
    const submissions = useAsync<Paginated<HomeworkSubmission>>(
        () => selected && can('homework.grade') ? api.getPaginated<HomeworkSubmission>(`/homework/${selected.id}/submissions?per_page=100`) : Promise.resolve({ items: [], meta: null }),
        [selected?.id],
    );

    const assignmentFor = (item: Homework) => assignments.find((assignment) => assignment.teacher_id === item.teacher_id && assignment.class_section_id === item.class_section_id && assignment.subject_id === item.subject_id);
    const assignmentValue = (assignment: HomeworkAssignment) => `${assignment.teacher_id}:${assignment.class_section_id}:${assignment.subject_id}`;
    const assignmentFromValue = (value: string) => assignments.find((item) => assignmentValue(item) === value);

    function resetForm() { setEditing(null); setForm(emptyForm); setError(null); }
    function beginEdit(item: Homework) {
        const assignment = assignmentFor(item);
        setEditing(item); setForm({ assignment: assignment ? assignmentValue(assignment) : '', title: item.title, body: item.body, due_at: toLocalDateTime(item.due_at) });
        setError(null); setNotice(null); window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function saveHomework(event: FormEvent) {
        event.preventDefault(); setSaving(true); setError(null); setNotice(null);
        const assignment = assignmentFromValue(form.assignment);
        if (!assignment) { setError('Choose a valid teacher, class, and subject assignment.'); setSaving(false); return; }
        const payload = { teacher_id: assignment.teacher_id, class_section_id: assignment.class_section_id, subject_id: assignment.subject_id, title: form.title, body: form.body, due_at: new Date(form.due_at).toISOString() };
        try {
            if (editing) await api.patch(`/homework/${editing.id}`, payload);
            else await api.post('/homework', payload);
            setNotice(editing ? 'Homework updated.' : 'Homework assigned.'); resetForm(); homework.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not save homework.'); }
        finally { setSaving(false); }
    }

    async function archive(item: Homework) {
        if (!window.confirm(`Archive “${item.title}”? Submitted work and grades will be preserved.`)) return;
        try { await api.delete(`/homework/${item.id}`); if (selected?.id === item.id) setSelected(null); setNotice('Homework archived.'); homework.reload(); }
        catch (err) { setError(err instanceof ApiError ? err.message : 'Could not archive homework.'); }
    }

    function openDetails(item: Homework) {
        setSelected(item);
        setRubric(item.rubric_criteria.length ? item.rubric_criteria.map((row) => ({ title: row.title, max_score: row.max_score })) : [{ title: '', max_score: 100 }]);
        setGradeDrafts({}); setError(null);
    }

    async function saveRubric(event: FormEvent) {
        event.preventDefault(); if (!selected) return;
        const criteria = rubric.filter((row) => row.title.trim()).map((row) => ({ title: row.title.trim(), max_score: Number(row.max_score) }));
        setSaving(true); setError(null);
        try {
            await api.put(`/homework/${selected.id}/rubric`, { criteria });
            const fresh = await api.get<Homework>(`/homework/${selected.id}`); setSelected(fresh); setNotice('Rubric saved.'); homework.reload();
        } catch (err) { setError(err instanceof ApiError ? err.message : 'Could not save the rubric.'); }
        finally { setSaving(false); }
    }

    function draftFor(submission: HomeworkSubmission): GradeDraft {
        return gradeDrafts[submission.id] ?? { grade: submission.grade?.toString() ?? '', feedback: submission.feedback ?? '', scores: Object.fromEntries(submission.rubric_scores.map((score) => [score.criterion_id, String(score.score)])) };
    }
    function updateDraft(id: number, changes: Partial<GradeDraft>) { setGradeDrafts((current) => ({ ...current, [id]: { ...draftFor(submissions.data!.items.find((item) => item.id === id)!), ...current[id], ...changes } })); }

    async function grade(event: FormEvent, submission: HomeworkSubmission) {
        event.preventDefault(); if (!selected) return;
        const draft = draftFor(submission);
        const rubricScores = selected.rubric_criteria.length ? selected.rubric_criteria.map((criterion) => ({ criterion_id: criterion.id, score: Number(draft.scores[criterion.id] ?? 0) })) : undefined;
        const gradeValue = rubricScores ? rubricScores.reduce((total, score) => total + score.score, 0) : Number(draft.grade);
        setSaving(true); setError(null);
        try { await api.post(`/homework/${selected.id}/submissions/${submission.id}/grade`, { grade: gradeValue, feedback: draft.feedback || null, rubric_scores: rubricScores }); setNotice('Submission graded.'); submissions.reload(); }
        catch (err) { setError(err instanceof ApiError ? err.message : 'Could not save the grade.'); }
        finally { setSaving(false); }
    }

    const records = homework.data?.items ?? [];
    const overdue = records.filter((item) => item.status === 'assigned' && new Date(item.due_at) < new Date()).length;
    const submitted = records.reduce((total, item) => total + (item.submissions_count ?? 0), 0);
    return <div className="inner-page">
        <PageHeader eyebrow="Learning workflow" title="Homework & assignments" description="Plan class work, publish expectations, review submissions, and return useful feedback." icon="homework" />
        <div className="stats-grid animate-in delay-one"><div className="stat-card"><span className="stat-icon violet"><Icon name="homework" /></span><div><small>Assignments</small><strong>{records.length}</strong><em>visible</em></div></div><div className="stat-card"><span className="stat-icon amber"><Icon name="clock" /></span><div><small>Past due</small><strong>{overdue}</strong><em>open</em></div></div><div className="stat-card"><span className="stat-icon mint"><Icon name="check" /></span><div><small>Submissions</small><strong>{submitted}</strong><em>received</em></div></div></div>

        {can('homework.create') && <Card className="animate-in delay-one"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Teacher workflow</span><CardTitle>{editing ? 'Edit assignment' : 'Create an assignment'}</CardTitle></div>{editing && <button className="secondary" onClick={resetForm}>Cancel edit</button>}</CardHeader><CardContent>
            {options.error && <Alert variant="error" className="mb-4">{options.error}</Alert>}
            {!options.loading && assignments.length === 0 && <Alert variant="warning" className="mb-4" title="No teaching assignments">Assign a teacher to a class and subject before publishing homework.</Alert>}
            <form onSubmit={saveHomework} className="grid gap-4 md:grid-cols-2">
                <Field label="Teacher · class · subject"><select className="field" value={form.assignment} onChange={(e) => setForm({ ...form, assignment: e.target.value })} required><option value="">Select an approved teaching assignment</option>{assignments.map((assignment) => <option key={assignmentValue(assignment)} value={assignmentValue(assignment)}>{assignment.teacher_name} · {assignment.class_label} · {assignment.subject_name}</option>)}</select></Field>
                <Field label="Due date and time"><input className="field" type="datetime-local" value={form.due_at} onChange={(e) => setForm({ ...form, due_at: e.target.value })} required /></Field>
                <Field label="Title"><input className="field" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} placeholder="e.g. Fractions practice" required /></Field>
                <Field label="Instructions"><textarea className="field min-h-24" value={form.body} onChange={(e) => setForm({ ...form, body: e.target.value })} placeholder="Explain the task, expected format, and success criteria..." required /></Field>
                <button className="primary flex items-center justify-center gap-2 md:col-span-2" disabled={saving || assignments.length === 0}>{saving ? <Loader size="sm" variant="spinner" /> : <Icon name={editing ? 'check' : 'plus'} className="h-4 w-4" />}{editing ? 'Save assignment' : 'Publish assignment'}</button>
            </form>{error && <Alert className="mt-4" variant="error" title="Action failed">{error}</Alert>}{notice && <Alert className="mt-4" variant="success" title="Done">{notice}</Alert>}
        </CardContent></Card>}

        <Card className="animate-in delay-two"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Course work</span><CardTitle>Assignment board</CardTitle></div><strong className="count-badge">{homework.data?.meta?.total ?? 0} assignments</strong></CardHeader><CardContent>
            <div className="grid gap-3 border-b border-white/5 pb-5 md:grid-cols-3"><input className="field" placeholder="Search title or instructions..." value={search} onChange={(e) => setSearch(e.target.value)} /><select className="field" value={status} onChange={(e) => setStatus(e.target.value)}><option value="">All statuses</option><option value="assigned">Assigned</option><option value="archived">Archived</option></select><select className="field" value={assignmentFilter} onChange={(e) => setAssignmentFilter(e.target.value)}><option value="">All classes and subjects</option>{assignments.map((assignment, index) => <option key={assignmentValue(assignment)} value={index}>{assignment.class_label} · {assignment.subject_name}</option>)}</select></div>
            {homework.loading && <div className="p-12"><Loader size="lg" variant="pulse" className="mx-auto" /></div>}{homework.error && <Alert className="mt-4" variant="error">{homework.error}</Alert>}
            {!homework.loading && records.length === 0 && <EmptyState icon="homework" title="No matching assignments" description="Create an assignment or change the filters." />}
            <div className="grid gap-3 pt-5 md:grid-cols-2">{records.map((item) => { const assignment = assignmentFor(item); const isOverdue = item.status === 'assigned' && new Date(item.due_at) < new Date(); return <article key={item.id} className="rounded-2xl border border-white/[0.07] bg-white/[0.02] p-5"><div className="flex items-start justify-between gap-3"><div><span className="text-[9px] font-bold uppercase tracking-widest text-violet-400">{assignment ? `${assignment.class_label} · ${assignment.subject_name}` : 'Class assignment'}</span><h3 className="mt-1 text-base font-semibold text-slate-100">{item.title}</h3></div><span className={`status-pill ${isOverdue ? '!bg-amber-500/10 !text-amber-400' : ''}`}>{isOverdue ? 'Past due' : item.status}</span></div><p className="mt-3 line-clamp-3 text-xs leading-6 text-slate-400">{item.body}</p><div className="mt-4 flex items-center justify-between border-t border-white/5 pt-4 text-[11px] text-slate-500"><span>Due {formatDate(item.due_at)}</span><span>{item.submissions_count ?? 0} submissions</span></div><div className="mt-4 flex flex-wrap gap-2"><button className="secondary px-3 py-2" onClick={() => openDetails(item)}>Review</button>{can('homework.create') && item.status !== 'archived' && <><button className="secondary px-3 py-2" onClick={() => beginEdit(item)}>Edit</button><button className="secondary px-3 py-2 text-rose-400" onClick={() => archive(item)}>Archive</button></>}</div></article>; })}</div>
        </CardContent></Card>

        {selected && <Card className="animate-in"><CardHeader><div><span className="text-[9px] font-bold uppercase tracking-widest text-slate-500">Review workspace</span><CardTitle>{selected.title}</CardTitle></div><button className="secondary" onClick={() => setSelected(null)}>Close</button></CardHeader><CardContent>
            <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                <div><h3 className="text-sm font-semibold text-slate-200">Instructions</h3><p className="mt-2 whitespace-pre-wrap text-xs leading-6 text-slate-400">{selected.body}</p><p className="mt-3 text-[11px] text-slate-500">Due {formatDate(selected.due_at)}</p>
                    {can('homework.create') && selected.status !== 'archived' && <form onSubmit={saveRubric} className="mt-6 border-t border-white/5 pt-5"><div className="flex items-center justify-between"><h3 className="text-sm font-semibold text-slate-200">Grading rubric</h3><span className="text-[10px] text-slate-500">Total {rubric.reduce((sum, row) => sum + Number(row.max_score || 0), 0)} / 100</span></div><div className="mt-3 grid gap-2">{rubric.map((row, index) => <div key={index} className="grid grid-cols-[1fr_90px_auto] gap-2"><input className="field" placeholder="Criterion" value={row.title} onChange={(e) => setRubric((current) => current.map((item, i) => i === index ? { ...item, title: e.target.value } : item))} /><input className="field" type="number" min="1" max="100" value={row.max_score} onChange={(e) => setRubric((current) => current.map((item, i) => i === index ? { ...item, max_score: Number(e.target.value) } : item))} /><button type="button" className="secondary px-3" onClick={() => setRubric((current) => current.filter((_, i) => i !== index))}>×</button></div>)}</div><div className="mt-3 flex gap-2"><button type="button" className="secondary px-3 py-2" onClick={() => setRubric((current) => [...current, { title: '', max_score: 10 }])}>Add criterion</button><button className="primary px-3 py-2" disabled={saving}>Save rubric</button></div></form>}
                </div>
                <div><h3 className="text-sm font-semibold text-slate-200">Student submissions</h3>{!can('homework.grade') && <Alert className="mt-3">Submission grading is available to authorized teachers and school administrators.</Alert>}{can('homework.grade') && submissions.loading && <div className="p-8"><Loader className="mx-auto" /></div>}{can('homework.grade') && submissions.error && <Alert className="mt-3" variant="error">{submissions.error}</Alert>}{can('homework.grade') && !submissions.loading && submissions.data?.items.length === 0 && <EmptyState icon="homework" title="No submissions yet" description="Student work will appear here after it is submitted." />}
                    <div className="mt-3 grid gap-3">{submissions.data?.items.map((submission) => { const draft = draftFor(submission); return <form key={submission.id} onSubmit={(event) => grade(event, submission)} className="rounded-xl border border-white/[0.07] bg-slate-950/30 p-4"><div className="flex justify-between gap-3"><strong className="text-xs text-slate-200">Student #{submission.student_id}</strong><span className="status-pill">{submission.status}</span></div><p className="my-3 whitespace-pre-wrap text-xs leading-5 text-slate-400">{submission.body}</p>{selected.rubric_criteria.length ? <div className="grid gap-2">{selected.rubric_criteria.map((criterion) => <label key={criterion.id} className="grid grid-cols-[1fr_90px] items-center gap-2 text-[11px] text-slate-400"><span>{criterion.title} (max {criterion.max_score})</span><input className="field" type="number" min="0" max={criterion.max_score} value={draft.scores[criterion.id] ?? ''} onChange={(e) => updateDraft(submission.id, { scores: { ...draft.scores, [criterion.id]: e.target.value } })} required /></label>)}</div> : <Field label="Grade out of 100"><input className="field" type="number" min="0" max="100" value={draft.grade} onChange={(e) => updateDraft(submission.id, { grade: e.target.value })} required /></Field>}<label className="mt-3 block"><span className="text-[10px] font-bold uppercase tracking-wide text-slate-400">Feedback</span><textarea className="field mt-1 min-h-20" value={draft.feedback} onChange={(e) => updateDraft(submission.id, { feedback: e.target.value })} placeholder="Give the student specific, useful feedback..." /></label><button className="primary mt-3 w-full" disabled={saving}>{submission.status === 'graded' ? 'Update grade' : 'Return grade'}</button></form>; })}</div>
                </div>
            </div>
        </CardContent></Card>}
    </div>;
}

function Field({ label, children }: { label: string; children: ReactNode }) { return <label className="flex flex-col gap-1.5"><span className="text-[10px] font-bold uppercase tracking-wide text-slate-400">{label}</span>{children}</label>; }
function formatDate(value: string) { return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)); }
function toLocalDateTime(value: string) { const date = new Date(value); const offset = date.getTimezoneOffset(); return new Date(date.getTime() - offset * 60_000).toISOString().slice(0, 16); }
