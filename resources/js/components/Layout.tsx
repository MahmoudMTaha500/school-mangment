import { NavLink, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '@/auth/AuthContext';
import { visibleNavItems } from '@/navigation';
import { Icon, type IconName } from '@/components/Icon';

const navIcons: Record<string, IconName> = {
    '/': 'overview',
    '/students': 'students',
    '/wallet': 'wallet',
    '/reports': 'reports',
    '/notifications': 'notifications',
    '/audit-logs': 'audit',
};

export function Layout() {
    const { me, baseUrl, logout, can } = useAuth();
    const items = visibleNavItems(can);
    const location = useLocation();
    const currentPage = items.find((item) => item.path === location.pathname)?.label || 'Workspace';

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <div className="brand-lockup">
                    <span className="brand-mark"><span>e</span></span>
                    <div><strong>Eduvera</strong><small>School workspace</small></div>
                </div>
                <nav className="sidebar-nav" aria-label="Primary">
                    <p className="nav-eyebrow">Workspace</p>
                    <div className="mobile-nav-scroll">
                        {items.map((item) => (
                            <NavLink
                                key={item.path}
                                to={item.path}
                                end={item.path === '/'}
                                className={({ isActive }) =>
                                    `nav-item ${isActive ? 'nav-item-active' : ''}`
                                }
                            >
                                <Icon name={navIcons[item.path]} className="nav-icon" />
                                {item.label}
                            </NavLink>
                        ))}
                    </div>
                </nav>
                <div className="sidebar-support">
                    <Icon name="sparkles" />
                    <div><strong>Need a hand?</strong><span>Your workspace is ready.</span></div>
                </div>
                <div className="sidebar-profile">
                    <div className="profile-avatar">{me?.name?.charAt(0).toUpperCase() || 'U'}</div>
                    <div className="profile-copy"><strong>{me?.name}</strong><span>{me?.roles.join(', ') || 'Member'}</span></div>
                    <button onClick={() => void logout()} className="signout-button" title={`Sign out from ${baseUrl}`} aria-label="Sign out">↗</button>
                </div>
            </aside>
            <div className="workspace">
                <header className="topbar">
                    <div className="topbar-title"><span>School Management</span><strong>{currentPage}</strong></div>
                    <div className="topbar-actions">
                        <label className="search-box">
                            <Icon name="search" />
                            <input placeholder="Search workspace" aria-label="Search workspace" />
                            <kbd>⌘ K</kbd>
                        </label>
                        <NavLink to="/notifications" className="icon-button" aria-label="Notifications">
                            <Icon name="notifications" /><span className="notification-dot" />
                        </NavLink>
                    </div>
                </header>
                <main className="main-content">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
