<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sales: {
        type: Array,
        required: true
    },
    tenant: {
        type: Object,
        required: true
    }
})

const searchTerm = ref('')

const filteredSales = computed(() => {
    if (!searchTerm.value) {
        return props.sales
    }

    const term = searchTerm.value.toLowerCase()

    return props.sales.filter(sale => {
        return (
            sale.folio.toLowerCase().includes(term) ||
            sale.id.toString().includes(term) ||
            (sale.customer_name && sale.customer_name.toLowerCase().includes(term))
        )
    })
})

function formatCurrency(value) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value)
}

function formatDate(date) {
    return new Date(date).toLocaleString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function paymentMethod(payment) {
    if (!payment) return '-'

    const methods = {
        cash: 'Efectivo',
        card: 'Tarjeta',
        transfer: 'Transferencia'
    }

    return methods[payment.method] ?? payment.method
}

function statusText(status) {
    const statuses = {
        paid: 'Pagada',
        pending: 'Pendiente',
        cancelled: 'Cancelada',
        completed: 'Completada'
    }

    return statuses[status] || status
}

function statusClass(status) {
    return {
        paid: 'bg-green-100 text-green-700',
        completed: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        cancelled: 'bg-red-100 text-red-700'
    }[status] || 'bg-gray-100 text-gray-700'
}

function clearSearch() {
    searchTerm.value = ''
}

const totalSales = computed(() => props.sales.length)
const totalFiltered = computed(() => filteredSales.value.length)
const totalAmount = computed(() => {
    return props.sales.reduce((sum, sale) => sum + sale.total, 0)
})
</script>

<template>
    <AppLayout>
        <div class="p-8">

            <!-- HEADER -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">
                        Ventas
                    </h1>
                    <p class="text-slate-500 mt-1">
                        Historial de ventas realizadas
                    </p>
                    <div class="flex gap-4 mt-2 text-sm text-slate-600">
                        <span>Total: {{ totalSales }} ventas</span>
                        <span>Monto total: {{ formatCurrency(totalAmount) }}</span>
                    </div>
                </div>

                <Link
                    href="/sales/pos"
                    class="rounded-2xl bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700 transition shadow-sm hover:shadow-md"
                >
                    + Nueva venta
                </Link>
            </div>

            <!-- CARD -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                <!-- SEARCH -->
                <div class="p-6 border-b border-slate-200">
                    <div class="relative">
                        <input
                            v-model="searchTerm"
                            placeholder="Buscar por folio, ID o cliente..."
                            class="w-full rounded-2xl border border-slate-300 px-5 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        />
                        <button
                            v-if="searchTerm"
                            @click="clearSearch"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition"
                        >
                            ×
                        </button>
                    </div>
                    <div v-if="searchTerm" class="mt-2 text-sm text-slate-500">
                        {{ totalFiltered }} resultados encontrados
                    </div>
                </div>

                <!-- TABLE -->
                <div v-if="filteredSales.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Folio
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Cliente
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Productos
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Total
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Pago
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Estado
                                </th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">
                                    Fecha
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="sale in filteredSales"
                                :key="sale.id"
                                class="hover:bg-slate-50 transition"
                            >
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ sale.folio }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ sale.customer_name || 'Cliente general' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ sale.items?.length || 0 }}
                                </td>
                                <td class="px-6 py-4 font-bold text-green-600">
                                    {{ formatCurrency(sale.total) }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ paymentMethod(sale.payments?.[0]) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusClass(sale.status)"
                                    >
                                        {{ statusText(sale.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ formatDate(sale.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- EMPTY -->
                <div v-else class="p-12 text-center">
                    <div class="text-6xl mb-4 text-slate-300">
                        📋
                    </div>
                    <h3 class="font-bold text-xl text-slate-700 mb-2">
                        {{ searchTerm ? 'No hay resultados' : 'No hay ventas' }}
                    </h3>
                    <p class="text-slate-500 mb-6">
                        {{ searchTerm
                            ? 'No se encontraron ventas con los filtros aplicados'
                            : 'Realiza tu primera venta en el punto de venta'
                        }}
                    </p>
                    <button
                        v-if="searchTerm"
                        @click="clearSearch"
                        class="rounded-2xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 hover:bg-slate-50 transition"
                    >
                        Limpiar búsqueda
                    </button>
                    <Link
                        v-else
                        href="/sales/pos"
                        class="inline-block rounded-2xl bg-green-600 px-6 py-3 font-semibold text-white hover:bg-green-700 transition shadow-sm hover:shadow-md"
                    >
                        + Nueva venta
                    </Link>
                </div>

                <!-- FOOTER -->
                <div v-if="filteredSales.length > 0" class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <span class="text-sm text-slate-500">
                            Mostrando {{ filteredSales.length }} de {{ totalSales }} ventas
                        </span>
                        <div class="flex gap-2">
                            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Anterior
                            </button>
                            <button class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700 transition">
                                1
                            </button>
                            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50 transition">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
