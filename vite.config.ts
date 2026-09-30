import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// PROCUREMENT_BUILD=backend: build the SPA (and the CMS page stylesheet) into the
// Laravel public directory, served from the same origin as the business API.
// Without it, the original relative-path static build is unchanged (demo/Pages).
const backendBuild = process.env.PROCUREMENT_BUILD === 'backend';
// Local Docker HTTPS origin (e.g. https://procurement.taleed.test) for HMR via the proxy.
const publicOrigin = process.env.VITE_PUBLIC_ORIGIN;
const publicUrl = publicOrigin ? new URL(publicOrigin) : undefined;

export default defineConfig({
  plugins: [react()],
  base: backendBuild ? '/spa/' : './', // Hash routes + relative assets: works below a static hosting subdirectory.
  server: {
    port: 5173,
    strictPort: true,
    ...(publicUrl
      ? {
          allowedHosts: [publicUrl.hostname],
          origin: publicUrl.origin,
          hmr: { protocol: 'wss', host: publicUrl.hostname, clientPort: Number(publicUrl.port || 443) },
        }
      : {}),
  },
  preview: { port: 4173, strictPort: true },
  build: {
    target: 'es2022',
    sourcemap: false,
    chunkSizeWarningLimit: 1500,
    ...(backendBuild
      ? {
          outDir: 'backend/public/spa',
          emptyOutDir: true,
          manifest: 'manifest.json',
          rollupOptions: { input: { index: 'index.html', cms: 'src/styles/cms-page.css' } },
        }
      : {}),
  },
});
