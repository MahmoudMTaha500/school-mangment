import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from '@/auth/AuthContext';
import { Layout } from '@/components/Layout';
import { ProtectedRoute } from '@/components/ProtectedRoute';
import { LoginPage } from '@/pages/LoginPage';
import { OverviewPage } from '@/pages/OverviewPage';
import { StudentsPage } from '@/pages/StudentsPage';
import { ParentsPage } from '@/pages/ParentsPage';
import { HomeworkPage } from '@/pages/HomeworkPage';
import { WalletPage } from '@/pages/WalletPage';
import { ReportsPage } from '@/pages/ReportsPage';
import { NotificationsPage } from '@/pages/NotificationsPage';
import { AuditLogsPage } from '@/pages/AuditLogsPage';

export function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <Routes>
                    <Route path="/login" element={<LoginPage />} />
                    <Route
                        element={
                            <ProtectedRoute>
                                <Layout />
                            </ProtectedRoute>
                        }
                    >
                        <Route path="/" element={<OverviewPage />} />
                        <Route
                            path="/students"
                            element={
                                <ProtectedRoute permission="sis.manage">
                                    <StudentsPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/parents"
                            element={
                                <ProtectedRoute permission="sis.manage">
                                    <ParentsPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/homework"
                            element={
                                <ProtectedRoute permission="homework.view">
                                    <HomeworkPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/wallet"
                            element={
                                <ProtectedRoute permission="wallet.view">
                                    <WalletPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/reports"
                            element={
                                <ProtectedRoute permission="reports.view">
                                    <ReportsPage />
                                </ProtectedRoute>
                            }
                        />
                        <Route path="/notifications" element={<NotificationsPage />} />
                        <Route
                            path="/audit-logs"
                            element={
                                <ProtectedRoute permission="school.manage">
                                    <AuditLogsPage />
                                </ProtectedRoute>
                            }
                        />
                    </Route>
                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}
