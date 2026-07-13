// Minimal ambient declaration for the subset of expo-secure-store used here.
// Lets the native adapter typecheck in CI without installing native modules;
// the real package (declared in package.json) supplies these at build time.
declare module 'expo-secure-store' {
    export function getItemAsync(key: string): Promise<string | null>;
    export function setItemAsync(key: string, value: string): Promise<void>;
    export function deleteItemAsync(key: string): Promise<void>;
}
