<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Management</title>
    <link rel="stylesheet" href="/dashboard.css">
    <script defer src="/dashboard.js"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<main class="mx-auto max-w-6xl p-6 lg:p-10">
    <header class="mb-10 flex flex-wrap items-center justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-[.2em] text-sky-400">School management</p><h1 class="mt-2 text-3xl font-semibold">Operations dashboard</h1></div>
        <button id="logout" class="rounded-lg border border-slate-700 px-4 py-2 text-sm hover:bg-slate-800">Clear session</button>
    </header>
    <section id="login-panel" class="grid gap-6 rounded-2xl border border-slate-800 bg-slate-900 p-6 md:grid-cols-2">
        <div><h2 class="text-xl font-semibold">Connect securely</h2><p class="mt-2 text-slate-400">Sign in to the Platform API or a school domain. Tokens stay only in this browser session.</p></div>
        <form id="login-form" class="grid gap-3">
            <select id="login-type" class="field"><option value="tenant">School user</option><option value="platform">Platform Admin</option></select>
            <input id="api-base" class="field" value="http://green-valley.localhost:8080/api/v1" aria-label="API base URL" required>
            <input id="email" class="field" type="email" placeholder="Email" required>
            <input id="password" class="field" type="password" placeholder="Password" required>
            <button class="primary" type="submit">Sign in</button>
        </form>
    </section>
    <section id="dashboard" class="hidden">
        <div id="status" class="mb-6 rounded-xl border border-slate-800 bg-slate-900 p-4 text-sm text-slate-300"></div>
        <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <section class="panel"><h2>Student directory</h2><form id="student-form" class="mt-4 grid gap-3"><input class="field" name="code" placeholder="Student code" required><input class="field" name="first_name" placeholder="First name" required><input class="field" name="last_name" placeholder="Last name" required><button class="primary">Add student</button></form><div id="students" class="mt-5"></div></section>
            <section class="panel"><h2>Quick links</h2><div class="mt-4 grid gap-3"><button class="secondary" data-path="/notifications">Notifications</button><button class="secondary" data-path="/reports/wallet?from=2026-01-01&to=2026-12-31">Wallet summary</button><button class="secondary" data-path="/reports/homework?class_section_id=1">Homework report</button></div><pre id="result" class="mt-5 max-h-80 overflow-auto rounded-xl bg-slate-950 p-4 text-xs text-sky-200"></pre></section>
        </div>
    </section>
</main>
</body>
</html>
