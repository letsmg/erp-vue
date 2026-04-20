import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    server: {
        port: 5174,
        host: '127.0.0.1', // Força IPv4
        strictPort: true
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'], // Se o arquivo for .ts
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // wayfinder({
        //     formVariants: true,
        //     generateOnRun: false,
        // }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
        extensions: ['.js', '.ts', '.vue', '.json'],
    },
    build: {
        // Ativa a minificação pesada para o servidor
        minify: 'esbuild',
        sourcemap: false, // Deixa o build mais leve no servidor
        chunkSizeWarningLimit: 1600,
        rollupOptions: {
            output: {
                // Organiza melhor os arquivos gerados
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});