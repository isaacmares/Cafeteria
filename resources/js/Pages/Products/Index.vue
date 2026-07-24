<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = page.props.tenant

const products = ref([])
const loading = ref(true)

// Variables para búsqueda y filtros
const searchTerm = ref('')
const filterStatus = ref('all')
const filteredProducts = ref([])

const fetchProducts = async () => {
    try {
        loading.value = true
        const response = await axios.get('/api/products', {
            withCredentials: true
        })
        products.value = response.data
        filteredProducts.value = response.data
    } catch (error) {
        console.error('Error al cargar productos:', error)
        // Datos de ejemplo
        products.value = [
            {
                id: 1,
                name: 'Café Americano',
                sku: 'CAF-001',
                barcode: '7501234567890',
                cost: 15.50,
                price: 35.00,
                stock: 25,
                image: null,
                is_active: true,
                created_at: '2024-01-01',
                updated_at: '2024-01-01'
            },
            {
                id: 2,
                name: 'Latte',
                sku: 'LAT-002',
                barcode: '7501234567891',
                cost: 20.00,
                price: 45.00,
                stock: 8,
                image: null,
                is_active: true,
                created_at: '2024-01-02',
                updated_at: '2024-01-02'
            },
            {
                id: 3,
                name: 'Croissant',
                sku: 'CRO-003',
                barcode: '7501234567892',
                cost: 12.00,
                price: 28.00,
                stock: 0,
                image: null,
                is_active: false,
                created_at: '2024-01-03',
                updated_at: '2024-01-03'
            }
        ]
        filteredProducts.value = products.value
    } finally {
        loading.value = false
    }
}

// Función de búsqueda
const filterProducts = () => {
    let result = products.value

    // Filtrar por búsqueda (nombre, SKU, código de barras)
    if (searchTerm.value.trim() !== '') {
        const term = searchTerm.value.toLowerCase().trim()
        result = result.filter(product =>
            product.name.toLowerCase().includes(term) ||
            (product.sku && product.sku.toLowerCase().includes(term)) ||
            (product.barcode && product.barcode.includes(term))
        )
    }

    // Filtrar por estado
    if (filterStatus.value === 'active') {
        result = result.filter(product => product.is_active === true)
    } else if (filterStatus.value === 'inactive') {
        result = result.filter(product => product.is_active === false)
    }

    filteredProducts.value = result
}

// Debounce para la búsqueda (evita muchas ejecuciones al escribir)
const debouncedFilter = debounce(filterProducts, 300)

// Watchers para detectar cambios
watch(searchTerm, () => {
    debouncedFilter()
})

watch(filterStatus, () => {
    filterProducts()
})

const toggleActive = async (product) => {
    try {
        await axios.patch(`/api/products/${product.id}`, {
            is_active: !product.is_active
        }, {
            withCredentials: true
        })
        product.is_active = !product.is_active
        // Actualizar también en filteredProducts
        const index = filteredProducts.value.findIndex(p => p.id === product.id)
        if (index !== -1) {
            filteredProducts.value[index].is_active = product.is_active
        }
    } catch (error) {
        console.error('Error al actualizar estado:', error)
        product.is_active = !product.is_active
    }
}

const deleteProduct = async (id) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return

    try {
        await axios.delete(`/api/products/${id}`, {
            withCredentials: true
        })
        products.value = products.value.filter(p => p.id !== id)
        filteredProducts.value = filteredProducts.value.filter(p => p.id !== id)
    } catch (error) {
        console.error('Error al eliminar producto:', error)
        alert('No se pudo eliminar el producto')
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount)
}

// Limpiar búsqueda
const clearSearch = () => {
    searchTerm.value = ''
    filterProducts()
}

onMounted(() => {
    fetchProducts()
})
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl p-8">

            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Productos
                    </h1>
                    <p class="mt-2 text-gray-500">
                        Gestiona el catálogo de productos de tu cafetería
                    </p>
                </div>
                <Link
                    href="/products/create"
                    class="rounded-xl bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700"
                >
                    + Nuevo producto
                </Link>
            </div>

            <!-- Tabla de productos -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">

                <!-- Barra de búsqueda y filtros -->
                <div class="border-b border-gray-200 p-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[200px] relative">
                            <input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Buscar por nombre, SKU o código de barras..."
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-10 transition focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100"
                            >
                            <button
                                v-if="searchTerm"
                                @click="clearSearch"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                ✕
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <select
                                v-model="filterStatus"
                                class="rounded-xl border border-gray-300 px-4 py-2.5 transition focus:border-green-500 focus:outline-none focus:ring-4 focus:ring-green-100"
                            >
                                <option value="all">Todos</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                            </select>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ filteredProducts.length }} productos
                        </span>
                    </div>
                </div>

                <!-- Estado de carga -->
                <div v-if="loading" class="p-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-green-600 border-t-transparent"></div>
                    <p class="mt-2 text-gray-500">Cargando productos...</p>
                </div>

                <!-- Tabla -->
                <div v-else-if="filteredProducts.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Producto</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">SKU</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Código</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Costo</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Precio</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Stock</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600">Estado</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="product.image" class="h-12 w-12 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0">
                                            <img :src="product.image" :alt="product.name" class="h-full w-full object-cover">
                                        </div>
                                        <div v-else class="h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg text-green-600">📦</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ product.name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ product.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ product.sku || '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ product.barcode || '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ formatCurrency(product.cost) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ formatCurrency(product.price) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="[
                                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                                        product.stock > 10 ? 'bg-green-100 text-green-700' :
                                        product.stock > 0 ? 'bg-yellow-100 text-yellow-700' :
                                        'bg-red-100 text-red-700'
                                    ]">
                                        {{ product.stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleActive(product)"
                                        :class="[
                                            'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition',
                                            product.is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="`/products/${product.id}/edit`"
                                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
                                            title="Editar"
                                        >
                                            ✏️
                                        </Link>
                                        <button
                                            @click="deleteProduct(product.id)"
                                            class="rounded-lg p-2 text-red-400 hover:bg-red-50 hover:text-red-600 transition"
                                            title="Eliminar"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Estado vacío con mensaje específico -->
                <div v-else class="p-12 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        {{ searchTerm || filterStatus !== 'all' ? 'No hay resultados' : 'No hay productos' }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ searchTerm || filterStatus !== 'all'
                            ? 'No se encontraron productos con los filtros seleccionados'
                            : 'Comienza agregando tu primer producto al catálogo'
                        }}
                    </p>
                    <div v-if="searchTerm || filterStatus !== 'all'" class="flex justify-center gap-3">
                        <button
                            @click="clearSearch"
                            class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-medium text-gray-700 hover:bg-gray-100 transition"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                    <Link
                        v-else
                        href="/products/create"
                        class="inline-block rounded-xl bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700"
                    >
                        + Agregar producto
                    </Link>
                </div>

                <!-- Paginación -->
                <div v-if="filteredProducts.length > 0" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            Mostrando {{ filteredProducts.length }} de {{ products.length }} productos
                        </span>
                        <div class="flex gap-2">
                            <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Anterior
                            </button>
                            <button class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700 transition">
                                1
                            </button>
                            <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 transition">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
