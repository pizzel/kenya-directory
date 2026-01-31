import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { renderToString } from 'react-dom/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from 'ziggy-js';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
        setup: ({ App, props }) => {
            global.route = (name, params, absolute, config) => {
                return route(name, params, absolute, {
                    ...props.initialPage.props.ziggy,
                    location: new URL(props.initialPage.props.ziggy.location),
                    ...config,
                });
            };

            return <App {...props} />;
        },
    })
);
