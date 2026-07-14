interface ImportMetaEnv {
    readonly VITE_TENANT_API_BASE_URL?: string;
    readonly VITE_PLATFORM_API_BASE_URL?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
