import * as ExpoSecureStore from 'expo-secure-store';
import type { SecureStore } from './secureStore';

/**
 * Production SecureStore backed by the platform keystore/keychain via
 * expo-secure-store. Injected at the app entry point; business logic never
 * imports this directly, keeping the rest of the app testable in Node.
 */
export const nativeSecureStore: SecureStore = {
    get: (key) => ExpoSecureStore.getItemAsync(key),
    set: (key, value) => ExpoSecureStore.setItemAsync(key, value),
    remove: (key) => ExpoSecureStore.deleteItemAsync(key),
};
