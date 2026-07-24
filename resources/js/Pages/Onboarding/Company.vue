<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    phone: '',
    address: '',
    rfc: '',
    logo: null,
});

const logoPreview = ref(null);

const onLogoChange = (event) => {
    const file = event.target.files[0];
    form.logo = file;

    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    } else {
        logoPreview.value = null;
    }
};

const submit = () => {
    form.post('/onboarding/company', {
        forceFormData: true,
    });
};
</script>

<template>
    <AuthLayout title="Configura tu empresa para continuar">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-slate-900">Datos de tu empresa</h2>
                <p class="mt-1 text-sm text-slate-500">Completa la información básica para comenzar a usar el sistema.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Nombre de la empresa <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1.5 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">Teléfono</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.phone }"
                    />
                    <p v-if="form.errors.phone" class="mt-1.5 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Dirección</label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.address }"
                    />
                    <p v-if="form.errors.address" class="mt-1.5 text-sm text-red-600">{{ form.errors.address }}</p>
                </div>

                <div>
                    <label for="rfc" class="mb-1.5 block text-sm font-medium text-slate-700">RFC</label>
                    <input
                        id="rfc"
                        v-model="form.rfc"
                        type="text"
                        maxlength="20"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 uppercase text-slate-900 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        :class="{ 'border-red-400': form.errors.rfc }"
                    />
                    <p v-if="form.errors.rfc" class="mt-1.5 text-sm text-red-600">{{ form.errors.rfc }}</p>
                </div>

                <div>
                    <label for="logo" class="mb-1.5 block text-sm font-medium text-slate-700">Logo</label>
                    <div class="flex items-center gap-4">
                        <div
                            v-if="logoPreview"
                            class="h-16 w-16 overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                        >
                            <img :src="logoPreview" alt="Vista previa del logo" class="h-full w-full object-cover" />
                        </div>
                        <input
                            id="logo"
                            type="file"
                            accept="image/*"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                            @change="onLogoChange"
                        />
                    </div>
                    <p v-if="form.errors.logo" class="mt-1.5 text-sm text-red-600">{{ form.errors.logo }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span v-if="form.processing">Guardando...</span>
                    <span v-else>Crear empresa</span>
                </button>
            </form>
        </div>
    </AuthLayout>
</template>
