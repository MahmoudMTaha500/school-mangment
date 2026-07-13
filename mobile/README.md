# School Management — Mobile (React Native)

Parent and student app for the tenant API. Talks to a school's tenant domain
(`https://<school>.<host>/api/v1`) using the same Sanctum bearer tokens as the
web app.

## Architecture

- `src/lib/` — framework-agnostic core, no `react-native` imports, unit-tested
  in Node:
  - `apiClient.ts` — typed client with `data`-envelope unwrapping and `ApiError`.
  - `secureStore.ts` — `SecureStore` interface + in-memory backend + session
    persistence helpers.
  - `secureStoreNative.ts` — production `SecureStore` over `expo-secure-store`
    (platform keychain/keystore).
  - `pushRegistration.ts` — registers/removes the device push token against
    `POST/DELETE /me/device-tokens` (the Phase 3 backend endpoint).
- `src/auth/AuthContext.tsx` — login, session restore, profile, logout.
- `src/screens/` — React Native UI (Home, Homework, Attendance, Wallet, Alerts).
- `src/App.tsx` — auth gate + tab navigation.

The split keeps all business logic testable without a simulator; screens are
verified by `tsc`.

## Verify (no device needed)

```bash
npm install
npm run typecheck   # tsc --noEmit
npm test            # vitest (headless core tests)
```

## Run on a device

Native `android/` and `ios/` folders are intentionally not committed. Generate
them, then run:

```bash
npx react-native init-template   # or `npx expo prebuild` if adopting Expo
npm run android                  # or: npm run ios
```

To enable push, request a token from your push provider (Expo Notifications or
`@react-native-firebase/messaging`) at startup and pass it to
`registerDeviceToken(api, token, platform)` after login; the backend fans out
via the FCM channel added in Phase 3.
