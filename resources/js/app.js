const session = {
    get base() { return sessionStorage.getItem('api-base'); },
    get token() { return sessionStorage.getItem('api-token'); },
    set(base, token) { sessionStorage.setItem('api-base', base.replace(/\/$/, '')); sessionStorage.setItem('api-token', token); },
    clear() { sessionStorage.removeItem('api-base'); sessionStorage.removeItem('api-token'); },
};

const $ = (selector) => document.querySelector(selector);
const result = $('#result');

async function api(path, options = {}) {
    const response = await fetch(`${session.base}${path}`, {
        ...options,
        headers: { Accept: 'application/json', Authorization: `Bearer ${session.token}`, ...(options.headers || {}) },
    });
    const body = response.status === 204 ? null : await response.json();
    if (!response.ok) throw new Error(body?.message || 'Request failed');
    return body;
}

async function loadStudents() {
    try {
        const payload = await api('/sis/students');
        $('#students').innerHTML = payload.data.data.map((student) => `<div class="flex justify-between border-b border-slate-800 py-2 text-sm"><span>${student.first_name} ${student.last_name}</span><span class="text-slate-400">${student.code}</span></div>`).join('') || '<p class="text-sm text-slate-500">No students yet.</p>';
    } catch { $('#students').innerHTML = '<p class="text-sm text-slate-500">Student access requires the School Admin role.</p>'; }
}

function showDashboard() {
    $('#login-panel').classList.add('hidden');
    $('#dashboard').classList.remove('hidden');
    $('#status').textContent = `Connected to ${session.base}`;
    loadStudents();
}

$('#login-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const base = $('#api-base').value;
    const path = $('#login-type').value === 'platform' ? '/platform/login' : '/auth/login';
    try {
        const response = await fetch(`${base.replace(/\/$/, '')}${path}`, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ email: $('#email').value, password: $('#password').value, device_name: 'dashboard' }) });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || 'Sign in failed');
        session.set(base, payload.token);
        showDashboard();
    } catch (error) { alert(error.message); }
});

$('#student-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    try { await api('/sis/students', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.fromEntries(form)) }); event.currentTarget.reset(); loadStudents(); } catch (error) { alert(error.message); }
});

document.querySelectorAll('[data-path]').forEach((button) => button.addEventListener('click', async () => {
    try { result.textContent = JSON.stringify(await api(button.dataset.path), null, 2); } catch (error) { result.textContent = error.message; }
}));

$('#logout').addEventListener('click', () => { session.clear(); location.reload(); });
if (session.token) showDashboard();
