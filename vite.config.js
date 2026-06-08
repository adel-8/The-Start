import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/global.css',
                'resources/css/about.css',
                'resources/css/addresses.css',
                'resources/css/admin.css',
                'resources/css/app.css',
                'resources/css/cart.css',
                'resources/css/checkout.css',
                'resources/css/contact.css',
                'resources/css/footer.css',
                'resources/css/home.css',
                'resources/css/product.css',
                'resources/css/Shop.css',
                'resources/css/signIn.css',   // typo variant if exists
                'resources/css/signUp.css',
                
                'resources/css/style.css',
               
            
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
                'resources/js/cards.js',
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