<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <AuthLayout title="Inicia sesión en tu cuenta">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">
            <h2 class="mb-6 text-xl font-semibold text-slate-900">Iniciar sesión</h2>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1.5 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.password }"
                    />
                    <p v-if="form.errors.password" class="mt-1.5 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="remember" class="ml-2 text-sm text-slate-600">Recordarme</label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span v-if="form.processing">Ingresando...</span>
                    <span v-else>Ingresar</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                ¿No tienes cuenta?
                <Link href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">Regístrate</Link>
            </p>
        </div>
    </AuthLayout>
</template>
