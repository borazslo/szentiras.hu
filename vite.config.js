import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: 'localhost'
    },
    plugins: [
        laravel({
            input: ['resources/assets/less/app.less', 
                'resources/assets/js/app.js', 
                'resources/assets/js/pages/home.js'],
            refresh: true,
        }),
    ],
});