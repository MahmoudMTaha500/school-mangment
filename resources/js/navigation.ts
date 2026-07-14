export interface NavItem {
    path: string;
    label: string;
    permission: string | null;
}

export const NAV_ITEMS: NavItem[] = [
    { path: '/', label: 'Overview', permission: null },
    { path: '/students', label: 'Students', permission: 'sis.manage' },
    { path: '/parents', label: 'Parents', permission: 'sis.manage' },
    { path: '/homework', label: 'Homework', permission: 'homework.view' },
    { path: '/wallet', label: 'Wallet', permission: 'wallet.view' },
    { path: '/reports', label: 'Reports', permission: 'reports.view' },
    { path: '/notifications', label: 'Notifications', permission: null },
    { path: '/audit-logs', label: 'Audit log', permission: 'school.manage' },
];

export function visibleNavItems(can: (permission: string) => boolean): NavItem[] {
    return NAV_ITEMS.filter((item) => item.permission === null || can(item.permission));
}
