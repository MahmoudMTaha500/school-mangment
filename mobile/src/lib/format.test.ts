import { describe, expect, it } from 'vitest';
import { formatDate, formatMinor } from './format';

describe('formatMinor', () => {
    it('renders minor units as a major-unit currency amount', () => {
        expect(formatMinor(5000, 'USD')).toContain('50');
    });
});

describe('formatDate', () => {
    it('returns a placeholder for a null date', () => {
        expect(formatDate(null)).toBe('—');
    });

    it('returns a placeholder for an unparseable date', () => {
        expect(formatDate('not-a-date')).toBe('—');
    });
});
