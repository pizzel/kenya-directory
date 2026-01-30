import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../Layouts/Layout';

export default function Home({ auth, ...props }) {
    return (
        <Layout>
            <Head title="Home" />
            <div className="min-h-[60vh] flex items-center justify-center p-5">
                <div className="bg-white p-12 rounded-2xl shadow-xl max-w-2xl w-full text-center border border-slate-100">
                    <div className="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i className="fas fa-check-circle text-3xl"></i>
                    </div>
                    <h1 className="text-4xl font-extrabold text-slate-900 mb-4 tracking-tight">React + Inertia is LIVE</h1>
                    <p className="text-lg text-slate-600 mb-8 leading-relaxed">
                        The foundation for Discover Kenya's modern frontend is successfully set up locally.
                    </p>
                    <div className="flex flex-wrap gap-4 justify-center">
                        <div className="bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700">✓ React 18</div>
                        <div className="bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700">✓ Inertia.js</div>
                        <div className="bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700">✓ Vite Compilation</div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
