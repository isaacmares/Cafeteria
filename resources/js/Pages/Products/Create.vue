<script setup>
import { reactive, ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = page.props.tenant

const props = defineProps({
    product: {
        type: Object,
        default: () => null
    }
})

const isEdit = computed(() => !!props.product)

const form = reactive({
    name: props.product?.name || '',
    sku: props.product?.sku || '',
    barcode: props.product?.barcode || '',
    cost: props.product?.cost || '',
    price: props.product?.price || '',
    image: null // Nuevo campo para el archivo
})

// Referencia para mostrar la previsualización (usa la imagen actual si existe en modo edición)
const imagePreview = ref(props.product?.image_url || null)
const fileInput = ref(null)

const loading = ref(false)
const success = ref(false)
const error = ref(null)

// Manejar la selección de imagen
const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.image = file
        imagePreview.value = URL.createObjectURL(file) // Crear URL temporal para previsualizar
    }
}

const save = async () => {
    if (!form.name.trim()) {
        error.value = 'El nombre del producto es obligatorio'
        return
    }

    if (!form.price || parseFloat(form.price) <= 0) {
        error.value = 'El precio de venta es obligatorio y debe ser mayor a 0'
        return
    }

    loading.value = true
    error.value = null
    success.value = false

    try {
        // Empaquetar los datos en FormData para soportar archivos
        const formData = new FormData()
        formData.append('name', form.name)
        formData.append('sku', form.sku)
        formData.append('barcode', form.barcode)
        formData.append('cost', form.cost)
        formData.append('price', form.price)

        if (form.image) {
            formData.append('image', form.image)
        }

        let response

        if (isEdit.value) {
            // Laravel requiere enviar archivos por POST simulando un PUT
            formData.append('_method', 'PUT')

            response = await axios.post(`/api/products/${props.product.id}`, formData, {
                withCredentials: true,
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        } else {
            response = await axios.post('/api/products', formData, {
                withCredentials: true,
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        }

        success.value = true

        if (!isEdit.value) {
            form.name = ''
            form.sku = ''
            form.barcode = ''
            form.cost = ''
            form.price = ''
            form.image = null
            imagePreview.value = null
            if (fileInput.value) fileInput.value.value = '' // Limpiar input file
        }

        setTimeout(() => {
            router.visit('/products')
        }, 2000)

    } catch (err) {
        console.error('Error al guardar:', err)
        error.value = err.response?.data?.message || 'Error al guardar el producto. Intenta de nuevo.'
    } finally {
        loading.value = false
    }
}

const cancel = () => {
    router.visit('/products')
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl p-8">

            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ isEdit ? 'Editar producto' : 'Nuevo producto' }}
                        </h1>
                        <p class="mt-2 text-gray-500">
                            {{ isEdit ? 'Actualiza la información del producto en el catálogo.' : 'Agrega un nuevo producto al catálogo de tu cafetería.' }}
                        </p>
                        <p v-if="isEdit" class="text-sm text-gray-500">
                            ID: #{{ product.id }}
                        </p>
                    </div>
                    <Link
                        href="/products"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-100 transition"
                    >
                        ← Volver
                    </Link>
                </div>
            </div>

            <!-- Mensaje de éxito -->
            <div
                v-if="success"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-700 flex items-center gap-3"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div>
                    <p class="font-semibold">
                        {{ isEdit ? '¡Producto actualizado exitosamente!' : '¡Producto guardado exitosamente!' }}
                    </p>
                    <p class="text-sm text-green-600">Redirigiendo al listado de productos...</p>
                </div>
            </div>

            <!-- Mensaje de error -->
            <div
                v-if="error"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 flex items-center gap-3"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>{{ error }}</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-8 py-6">
                    <h2 class="text-lg font-semibold">
                        Información del producto
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-6 p-8 md:grid-cols-2">

                    <!-- Sección de carga de imagen -->
                    <div class="md:col-span-2 flex flex-col mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Imagen del producto
                        </label>
                        <div class="flex items-center gap-6">
                            <!-- Contenedor de previsualización -->
                            <div class="h-32 w-32 shrink-0 overflow-hidden rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="Vista previa"
                                    class="h-full w-full object-cover"
                                />
                                <svg v-else class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            <!-- Botón de subida -->
                            <div class="flex flex-col">
                                <label class="cursor-pointer rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition text-center inline-block">
                                    <span>Seleccionar imagen</span>
                                    <input
                                        type="file"
                                        class="hidden"
                                        accept="image/png, image/jpeg, image/jpg, image/webp"
                                        @change="handleImageUpload"
                                        ref="fileInput"
                                    />
                                </label>
                                <p class="mt-2 text-xs text-gray-500">Recomendado: PNG, JPG o WEBP. Máx 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Nombre del producto *
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej. Café Americano"
                            :class="[
                                'w-full rounded-xl border px-4 py-3 transition focus:outline-none focus:ring-4',
                                error && !form.name ? 'border-red-500 focus:ring-red-100' : 'border-gray-300 focus:border-amber-500 focus:ring-amber-100'
                            ]"
                            required
                        />
                        <p v-if="error && !form.name" class="mt-1 text-sm text-red-600">
                            El nombre es obligatorio
                        </p>
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
                        />
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
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Costo
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
                            />
                        </div>
                        <p v-if="isEdit && product.cost" class="mt-1 text-xs text-gray-500">
                            Costo actual: ${{ parseFloat(product.cost).toFixed(2) }}
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
                                :class="[
                                    'w-full rounded-xl border px-4 py-3 pl-8 transition focus:outline-none focus:ring-4',
                                    error && (!form.price || parseFloat(form.price) <= 0) ? 'border-red-500 focus:ring-red-100' : 'border-gray-300 focus:border-amber-500 focus:ring-amber-100'
                                ]"
                                required
                            />
                        </div>
                        <p v-if="error && (!form.price || parseFloat(form.price) <= 0)" class="mt-1 text-sm text-red-600">
                            El precio debe ser mayor a 0
                        </p>
                        <p v-if="isEdit && product.price" class="mt-1 text-xs text-gray-500">
                            Precio actual: ${{ parseFloat(product.price).toFixed(2) }}
                        </p>
                    </div>

                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 bg-gray-50 px-8 py-5">

                    <button
                        @click="cancel"
                        type="button"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 hover:bg-gray-100 transition"
                    >
                        Cancelar
                    </button>

                    <button
                        @click="save"
                        :disabled="loading"
                        class="rounded-xl bg-amber-600 px-6 py-2.5 font-semibold text-white transition hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg v-if="loading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        {{ loading ? (isEdit ? 'Actualizando...' : 'Guardando...') : (isEdit ? 'Actualizar producto' : 'Guardar producto') }}
                    </button>

                </div>

            </div>

        </div>
    </AppLayout>
</template>
