import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Ziggy } from './ziggy';
import { route } from 'ziggy-js';

// Globalize route for convenience
window.route = (name, params, absolute, config = Ziggy) => route(name, params, absolute, config);

createInertiaApp({
    title: (title) => `${title} - Discover Kenya`,
    resolve: (name) => {
        console.log('Resolving component:', name);
        return resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx'));
    },
    setup({ el, App, props }) {
        console.log('Setting up Inertia app');
        const root = createRoot(el);
        root.render(<App {...props} />);
        console.log('Render call completed');
    },
    progress: {
        color: '#2563eb',
    },
});
