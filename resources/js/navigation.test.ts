import { describe, expect, it } from 'vitest';
import { visibleNavItems } from './navigation';

describe('visibleNavItems', () => {
    it('shows only public items to a user with no permissions', () => {
        const items = visibleNavItems(() => false);
        expect(items.map((i) => i.path)).toEqual(['/', '/notifications']);
    });

    it('reveals gated items exactly when the permission is granted', () => {
        const granted = new Set(['sis.manage', 'wallet.view']);
        const items = visibleNavItems((permission) => granted.has(permission));
        const paths = items.map((i) => i.path);
        expect(paths).toContain('/students');
        expect(paths).toContain('/wallet');
        expect(paths).not.toContain('/reports');
        expect(paths).not.toContain('/audit-logs');
    });

    it('shows everything to a full-permission admin', () => {
        const items = visibleNavItems(() => true);
        expect(items).toHaveLength(6);
    });
});
