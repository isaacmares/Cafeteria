<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sale: {
        type: Object,
        required: true
    },
    tenant: {
        type: Object,
        required: true
    }
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value)
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const statusText = (status) => {
    const statuses = {
        paid: 'Pagada',
        pending: 'Pendiente',
        cancelled: 'Cancelada',
        completed: 'Completada'
    }
    return statuses[status] || status
}

const statusClass = (status) => {
    return {
        paid: 'bg-green-100 text-green-700',
        completed: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        cancelled: 'bg-red-100 text-red-700'
    }[status] || 'bg-gray-100 text-gray-700'
}

const paymentMethodText = (payment) => {
    if (!payment) return '-'
    const methods = {
        cash: 'Efectivo',
        card: 'Tarjeta',
        transfer: 'Transferencia'
    }
    return methods[payment.method] || payment.method
}

const subtotal = computed(() => {
    if (!props.sale.items) return 0
    return props.sale.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const tax = computed(() => {
    return props.sale.tax || 0
})

const total = computed(() => {
    return props.sale.total || 0
})

const handlePrint = () => {
    window.print()
}
</script>

<template>
    <AppLayout>
        <div class="p-8">

            <!-- HEADER -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">
                        Ticket de Venta
                    </h1>
                    <p class="text-slate-500 mt-1">
                        Folio: {{ sale.folio || 'N/A' }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        @click="handlePrint"
                        class="rounded-2xl bg-amber-600 px-6 py-3 font-semibold text-white hover:bg-amber-700 transition shadow-sm hover:shadow-md"
                    >
                        Imprimir
                    </button>
                    <Link
                        href="/sales"
                        class="rounded-2xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 hover:bg-slate-50 transition"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <!-- TICKET -->
            <div id="ticket-content" class="max-w-2xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-8">
                    <!-- ENCABEZADO DEL TICKET -->
                    <div class="text-center border-b border-dashed border-slate-200 pb-6 mb-6">
                        <h2 class="text-3xl font-bold text-amber-600">
                            {{ tenant?.name || 'Cafetería' }}
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            {{ tenant?.address || 'Dirección no registrada' }}
                        </p>
                        <p class="text-sm text-slate-500">
                            Tel: {{ tenant?.phone || 'N/A' }}
                        </p>
                        <div class="mt-3 pt-3 border-t border-dashed border-slate-200">
                            <p class="text-xs text-slate-400">
                                Folio: {{ sale.folio || 'N/A' }}
                            </p>
                            <p class="text-xs text-slate-400">
                                Fecha: {{ formatDate(sale.created_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DEL CLIENTE -->
                    <div v-if="sale.customer_name" class="mb-6 p-4 bg-slate-50 rounded-xl">
                        <p class="text-sm font-medium text-slate-700">
                            Cliente:
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ sale.customer_name }}
                        </p>
                        <p v-if="sale.customer_email" class="text-sm text-slate-600">
                            {{ sale.customer_email }}
                        </p>
                    </div>

                    <!-- PRODUCTOS -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">
                            Productos
                        </h4>
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                                            Producto
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                                            Cant.
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                                            Precio
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="item in sale.items" :key="item.id">
                                        <td class="px-4 py-3 text-slate-700">
                                            {{ item.name }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-slate-600">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-slate-600">
                                            {{ formatCurrency(item.price) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-900">
                                            {{ formatCurrency(item.price * item.quantity) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TOTALES -->
                    <div class="border-t border-dashed border-slate-200 pt-4 mb-6">
                        <div class="flex justify-between text-sm py-2">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="text-slate-700">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div v-if="tax > 0" class="flex justify-between text-sm py-2">
                            <span class="text-slate-600">IVA ({{ (tax / subtotal * 100).toFixed(0) }}%)</span>
                            <span class="text-slate-700">{{ formatCurrency(tax) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold pt-3 border-t border-slate-200">
                            <span class="text-slate-900">Total</span>
                            <span class="text-amber-600">{{ formatCurrency(total) }}</span>
                        </div>
                    </div>

                    <!-- MÉTODO DE PAGO -->
                    <div v-if="sale.payments && sale.payments.length > 0" class="mb-6 p-4 bg-slate-50 rounded-xl">
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <span class="font-medium text-slate-700">Método de pago:</span>
                            <span class="text-slate-600">{{ paymentMethodText(sale.payments[0]) }}</span>

                            <span class="font-medium text-slate-700">Monto:</span>
                            <span class="text-slate-600">{{ formatCurrency(sale.payments[0].amount || sale.total) }}</span>

                            <span v-if="sale.payments[0]?.change" class="font-medium text-slate-700">Cambio:</span>
                            <span v-if="sale.payments[0]?.change" class="text-slate-600">{{ formatCurrency(sale.payments[0].change) }}</span>
                        </div>
                    </div>

                    <!-- ESTADO -->
                    <div class="flex items-center justify-between pt-4 border-t border-dashed border-slate-200">
                        <span class="text-sm font-medium text-slate-700">Estado:</span>
                        <span
                            class="rounded-full px-4 py-1.5 text-sm font-semibold"
                            :class="statusClass(sale.status)"
                        >
                            {{ statusText(sale.status) }}
                        </span>
                    </div>

                    <!-- FOOTER DEL TICKET -->
                    <div class="text-center border-t border-dashed border-slate-200 pt-6 mt-6">
                        <p class="text-sm font-medium text-slate-600">
                            ¡Gracias por tu compra!
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Este ticket es tu comprobante de compra
                        </p>
                        <div class="mt-2">
                            <span class="text-xs text-slate-400">
                                {{ new Date().toLocaleString('es-MX') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    /* Ocultar todo excepto el ticket */
    body * {
        visibility: hidden;
    }

    #ticket-content, #ticket-content * {
        visibility: visible;
    }

    #ticket-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100% !important;
        padding: 20px !important;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    /* Ocultar elementos que no deben imprimirse */
    .p-8 > div:first-child {
        display: none !important;
    }
}
</style>
