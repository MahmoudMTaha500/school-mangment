import { afterEach, describe, expect, it, vi } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import { AuthProvider } from '@/auth/AuthContext';
import { LoginPage } from './LoginPage';

function renderLogin() {
    return render(
        <MemoryRouter>
            <AuthProvider>
                <LoginPage />
            </AuthProvider>
        </MemoryRouter>,
    );
}

afterEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
});

describe('LoginPage', () => {
    it('posts credentials to the tenant login endpoint and stores the session', async () => {
        const fetchMock = vi
            .spyOn(globalThis, 'fetch')
            .mockImplementation(() => Promise.resolve(new Response(JSON.stringify({ token: 'abc123' }), { status: 200 })));

        renderLogin();
        await userEvent.type(screen.getByLabelText('Email'), 'admin@school.test');
        await userEvent.type(screen.getByLabelText('Password'), 'super-secret-pw');
        await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

        await waitFor(() => expect(fetchMock).toHaveBeenCalled());
        const [url, init] = fetchMock.mock.calls[0];
        expect(String(url)).toMatch(/\/auth\/login$/);
        expect(JSON.parse(String(init?.body))).toMatchObject({ email: 'admin@school.test' });
        await waitFor(() => expect(sessionStorage.getItem('sms.session')).toContain('abc123'));
    });

    it('surfaces a server error message without storing a session', async () => {
        vi.spyOn(globalThis, 'fetch').mockResolvedValue(
            new Response(JSON.stringify({ message: 'The supplied credentials are invalid.' }), { status: 422 }),
        );

        renderLogin();
        await userEvent.type(screen.getByLabelText('Email'), 'bad@school.test');
        await userEvent.type(screen.getByLabelText('Password'), 'wrong-password-1');
        await userEvent.click(screen.getByRole('button', { name: /sign in/i }));

        expect(await screen.findByRole('alert')).toHaveTextContent(/invalid/i);
        expect(sessionStorage.getItem('sms.session')).toBeNull();
    });
});
