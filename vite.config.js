import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
               
            
                //'resources/js/admin-analytics.js',
                'resources/js/analytics.js',
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                'resources/js/cart.js',
                'resources/js/checkout.js',
                'resources/js/contact.js',
                'resources/js/dashboard.js',
                'resources/js/home.js',
                'resources/js/navbar.js',
                'resources/js/product.js',
                'resources/js/Shop.js',
                'resources/js/signin.js',
                'resources/js/signup.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});