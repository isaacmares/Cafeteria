<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link, usePage, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = page.props.tenant

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
})

const form = reactive({
    name: props.product.name || '',
    sku: props.product.sku || '',
    barcode: props.product.barcode || '',
    cost: props.product.cost || '',
    price: props.product.price || '',
    stock: props.product.stock || 0,
    is_active: props.product.is_active ?? true,
})

const loading = ref(false)
const success = ref(false)

const updateProduct = async () => {
    loading.value = true
    success.value = false

    try {
        await axios.put(`/api/products/${props.product.id}`, form, {
            withCredentials: true
        })

        success.value = true

        // Redirigir después de 1.5 segundos
        setTimeout(() => {
            window.location.href = '/products'
        }, 1500)
    } catch (error) {
        console.error('Error al actualizar producto:', error)
        alert('Ocurrió un error al actualizar el producto')
    } finally {
        loading.value = false
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount)
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl p-8">

            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Editar producto
                        </h1>
                        <p class="mt-2 text-gray-500">
                            Actualiza la información del producto en el catálogo de tu cafetería.
                        </p>
                    </div>
                    <Link
                        href="/products"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition hover:bg-gray-100"
                    >
                        ← Volver
                    </Link>
                </div>
            </div>

            <!-- Mensaje de éxito -->
            <div v-if="success" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700">
                ✅ Producto actualizado exitosamente. Redirigiendo...
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-8 py-6">
                    <h2 class="text-lg font-semibold">
                        Información del producto
                    </h2>
                    <p class="text-sm text-gray-500">
                        ID: #{{ product.id }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 p-8 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Nombre del producto *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej. Café Americano"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                            required
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            SKU
                        </label>
                        <input
                            v-model="form.sku"
                            type="text"
                            placeholder="CAF-001"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Código de barras
                        </label>
                        <input
                            v-model="form.barcode"
                            type="text"
                            placeholder="7501234567890"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Costo *
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input
                                v-model="form.cost"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-300 pl-8 pr-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                                required
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Costo actual: {{ formatCurrency(product.cost) }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Precio de venta *
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-300 pl-8 pr-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                                required
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Precio actual: {{ formatCurrency(product.price) }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Stock
                        </label>
                        <input
                            v-model="form.stock"
                            type="number"
                            min="0"
                            placeholder="0"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 transition focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Stock actual: {{ product.stock }} unidades
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Estado
                        </label>
                        <div class="flex items-center gap-4 pt-1">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    v-model="form.is_active"
                                    type="radio"
                                    :value="true"
                                    class="h-4 w-4 text-amber-600 focus:ring-amber-500"
                                >
                                <span class="text-sm text-gray-700">Activo</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    v-model="form.is_active"
                                    type="radio"
                                    :value="false"
                                    class="h-4 w-4 text-amber-600 focus:ring-amber-500"
                                >
                                <span class="text-sm text-gray-700">Inactivo</span>
                            </label>
                            <span
                                :class="[
                                    'ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                    product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                                ]"
                            >
                                Estado actual: {{ product.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 bg-gray-50 px-8 py-5">

                    <Link
                        href="/products"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition hover:bg-gray-100"
                    >
                        Cancelar
                    </Link>

                    <button
                        @click="updateProduct"
                        :disabled="loading"
                        class="rounded-xl bg-amber-600 px-6 py-2.5 font-semibold text-white transition hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading" class="flex items-center gap-2">
                            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            Actualizando...
                        </span>
                        <span v-else>Actualizar producto</span>
                    </button>

                </div>

            </div>

        </div>
    </AppLayout>
</template>
