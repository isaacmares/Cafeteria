<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { cartStore } from '@/Stores/cartStore'
import PaymentModal from '@/Components/Sales/PaymentModal.vue'
import axios from 'axios'

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    }
})

const cart = cartStore
const search = ref('')
const paymentOpen = ref(false)
const customerWindow = ref(null)
const showSuccessMessage = ref(false)
const lastSaleData = ref(null)

console.log('POS montado', { products: props.products.length, tenant: props.tenant })

// Agregar producto al carrito
const addProduct = (product) => {
    console.log('Agregando producto:', product.name)
    cart.addItem(product)
    openCustomerView()
}

const openCustomerView = () => {
    if (!customerWindow.value || customerWindow.value.closed) {
        customerWindow.value = window.open(
            '/sales/customer-view',
            'customerView',
            'width=600,height=900,menubar=no,toolbar=no,location=no,resizable=yes'
        )
    }
}

const increase = (item) => {
    console.log('Aumentar cantidad:', item.name)
    cart.updateQuantity(item.id, item.quantity + 1)
}

const decrease = (item) => {
    console.log('Disminuir cantidad:', item.name)
    cart.updateQuantity(item.id, item.quantity - 1)
}

const removeProduct = (id) => {
    console.log('Eliminar producto:', id)
    cart.removeItem(id)
}

const clearCart = () => {
    console.log('Limpiar carrito')
    cart.clearCart()
}

// Filtrar productos por búsqueda
const filteredProducts = computed(() => {
    if (!search.value) {
        return props.products
    }
    const term = search.value.toLowerCase()
    return props.products.filter(p =>
        p.name.toLowerCase().includes(term) ||
        (p.sku && p.sku.toLowerCase().includes(term))
    )
})

// Formatear moneda
const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value)
}

// Imprimir ticket
const printTicket = (sale) => {
    console.log('Imprimiendo ticket:', sale)
    if (!sale) return

    const printContent = `
        <html>
            <head>
                <style>
                    body {
                        font-family: 'Courier New', monospace;
                        font-size: 12px;
                        margin: 0;
                        padding: 20px;
                        width: 80mm;
                        margin: 0 auto;
                    }
                    .header {
                        text-align: center;
                        border-bottom: 1px dashed #000;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                    }
                    .header h1 {
                        font-size: 18px;
                        margin: 0;
                    }
                    .header p {
                        margin: 2px 0;
                        font-size: 10px;
                    }
                    .items {
                        width: 100%;
                        margin: 10px 0;
                    }
                    .items th {
                        text-align: left;
                        font-size: 10px;
                        border-bottom: 1px solid #000;
                    }
                    .items td {
                        padding: 3px 0;
                        font-size: 11px;
                    }
                    .items .qty { text-align: center; }
                    .items .price { text-align: right; }
                    .items .total { text-align: right; }
                    .totals {
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                    }
                    .totals .row {
                        display: flex;
                        justify-content: space-between;
                        padding: 2px 0;
                    }
                    .totals .grand-total {
                        font-size: 16px;
                        font-weight: bold;
                        border-top: 1px solid #000;
                        padding-top: 5px;
                        margin-top: 5px;
                    }
                    .payment-info {
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                    }
                    .footer {
                        text-align: center;
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                        font-size: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>${sale.tenant_name || 'Cafetería'}</h1>
                    <p>${sale.tenant_address || ''}</p>
                    <p>Tel: ${sale.tenant_phone || ''}</p>
                    <p>--------------------------------</p>
                    <p>Ticket: ${sale.folio || sale.id}</p>
                    <p>Fecha: ${new Date(sale.created_at).toLocaleString('es-MX')}</p>
                    <p>Cliente: ${sale.customer_name || 'Cliente general'}</p>
                </div>

                <table class="items">
                    <tr>
                        <th>Producto</th>
                        <th class="qty">Cant</th>
                        <th class="price">Precio</th>
                        <th class="total">Total</th>
                    </tr>
                    ${sale.items.map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td class="qty">${item.quantity}</td>
                            <td class="price">${formatCurrency(item.price)}</td>
                            <td class="total">${formatCurrency(item.price * item.quantity)}</td>
                        </tr>
                    `).join('')}
                </table>

                <div class="totals">
                    <div class="row grand-total">
                        <span>TOTAL</span>
                        <span>${formatCurrency(sale.total)}</span>
                    </div>
                </div>

                <div class="payment-info">
                    <div class="row">
                        <span>Método de pago</span>
                        <span>${sale.payment_method || 'Efectivo'}</span>
                    </div>
                    ${sale.received ? `
                        <div class="row">
                            <span>Recibido</span>
                            <span>${formatCurrency(sale.received)}</span>
                        </div>
                    ` : ''}
                    ${sale.change ? `
                        <div class="row">
                            <span>Cambio</span>
                            <span>${formatCurrency(sale.change)}</span>
                        </div>
                    ` : ''}
                </div>

                <div class="footer">
                    <p>¡Gracias por su compra!</p>
                    <p>Vuelva pronto</p>
                </div>
            </body>
        </html>
    `

    const printWindow = window.open('', '_blank', 'width=400,height=600')
    printWindow.document.write(printContent)
    printWindow.document.close()
    printWindow.focus()

    setTimeout(() => {
        printWindow.print()
    }, 500)
}

// Manejar pago exitoso
const handlePaymentSuccess = (sale) => {
    console.log('Pago exitoso:', sale)
    lastSaleData.value = sale
    clearCart()
    paymentOpen.value = false

    showSuccessMessage.value = true
    console.log('Mostrando mensaje de éxito')

    if (sale) {
        sale.tenant_name = props.tenant?.name || 'Cafetería'
        sale.tenant_address = props.tenant?.address || ''
        sale.tenant_phone = props.tenant?.phone || ''
        printTicket(sale)
    }

    setTimeout(() => {
        showSuccessMessage.value = false
        console.log('Ocultando mensaje de éxito')
    }, 5000)


}

onMounted(() => {
    console.log('POS montado, sincronizando...')
    cart.syncFromStorage()
})

const openPayment = async () => {
    if (cart.state.items.length === 0) return

    // Verificar si hay caja abierta
    try {
        const response = await axios.get('/api/cash-register/current', {
            withCredentials: true
        })

        if (!response.data || response.data.status !== 'open') {
            alert('No hay una caja abierta. Debes abrir la caja primero en el módulo de Caja.')
            return
        }

        cart.complete()
        paymentOpen.value = true
    } catch (error) {
        alert('Error al verificar la caja. Asegúrate de tener una caja abierta.')
        console.error(error)
    }
}
</script>

<template>
    <AppLayout>
        <div class="h-full flex gap-4 p-4 bg-slate-50">

            <!-- MENSAJE DE ÉXITO -->
            <div
                v-if="showSuccessMessage"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-green-500 text-white px-8 py-4 rounded-2xl shadow-2xl text-center animate-fade-in"
            >
                <div class="text-3xl font-bold">Venta Completada</div>
                <div class="text-sm mt-1">Ticket impreso correctamente</div>
                <div class="text-xs mt-2 opacity-75">Folio: {{ lastSaleData?.folio || lastSaleData?.id }}</div>
            </div>

            <!-- PANEL DE PRODUCTOS -->
            <div class="flex-1 bg-white rounded-3xl shadow-lg flex flex-col overflow-hidden">

                <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-green-50 to-white">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    v-model="search"
                                    placeholder="Buscar productos por nombre o SKU..."
                                    class="w-full rounded-2xl border-slate-200 bg-white pl-12 pr-4 py-3.5 text-base outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm"
                                />
                            </div>
                        </div>

                        <button
                            @click="openCustomerView"
                            class="px-5 py-3 rounded-2xl bg-green-100 text-green-700 hover:bg-green-200 transition font-medium flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Cliente
                        </button>
                    </div>

                    <div class="mt-3 text-sm text-slate-500">
                        {{ filteredProducts.length }} productos disponibles
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div v-if="filteredProducts.length === 0" class="text-center py-20">
                        <svg class="w-24 h-24 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-slate-700">No hay productos</h3>
                        <p class="text-slate-400">Prueba con otra búsqueda</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <div
                            v-for="product in filteredProducts"
                            :key="product.id"
                            @click="addProduct(product)"
                            class="group cursor-pointer bg-white rounded-2xl border border-slate-200 p-4 transition-all hover:shadow-xl hover:border-green-300 hover:-translate-y-1"
                        >
                            <div class="aspect-square w-full rounded-2xl bg-gradient-to-br from-green-50 to-slate-100 flex items-center justify-center overflow-hidden mb-3">
                                <img
                                    v-if="product.image"
                                    :src="product.image"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                />
                                <svg v-else class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>

                            <div class="text-center">
                                <h3 class="font-semibold text-slate-800 text-sm truncate" :title="product.name">
                                    {{ product.name }}
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ product.sku || 'Sin SKU' }}
                                </p>
                                <p class="mt-2 text-lg font-bold text-green-600">
                                    {{ formatCurrency(product.price) }}
                                </p>

                                <div class="mt-2">
                                    <span
                                        class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                                        :class="[
                                            product.stock > 10 ? 'bg-green-100 text-green-700' :
                                            product.stock > 0 ? 'bg-yellow-100 text-yellow-700' :
                                            'bg-red-100 text-red-700'
                                        ]"
                                    >
                                        Stock: {{ product.stock }}
                                    </span>
                                </div>

                                <div class="mt-3 opacity-0 group-hover:opacity-100 transition">
                                    <span class="inline-block w-full py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                                        Agregar
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL DEL CARRITO CON BOTÓN FIJO -->
            <div class="w-[420px] bg-white rounded-3xl shadow-lg flex flex-col relative">

                <!-- Header del carrito -->
                <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-green-50 to-white flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">
                                Carrito
                            </h2>
                            <p class="text-sm text-slate-500">
                                {{ cart.state.items.length }} productos
                            </p>
                        </div>
                        <div class="text-sm font-semibold text-green-600">
                            {{ formatCurrency(cart.total.value) }}
                        </div>
                    </div>
                </div>

                <!-- Lista de productos (scrollable) -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3 pb-36">
                    <div v-if="cart.state.items.length === 0" class="text-center py-20">
                        <svg class="w-24 h-24 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p class="text-slate-400 font-medium">Carrito vacío</p>
                        <p class="text-sm text-slate-300">Agrega productos desde el catálogo</p>
                    </div>

                    <div
                        v-for="item in cart.state.items"
                        :key="item.id"
                        class="bg-slate-50 rounded-2xl p-4 border border-slate-200 hover:border-green-200 transition"
                    >
                        <div class="flex items-start gap-3">
                            <div class="h-12 w-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-800 truncate" :title="item.name">
                                    {{ item.name }}
                                </h4>
                                <p class="text-sm text-green-600 font-medium">
                                    {{ formatCurrency(item.price) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="decrease(item)"
                                    class="h-8 w-8 rounded-xl bg-slate-200 hover:bg-slate-300 transition flex items-center justify-center font-bold text-slate-600"
                                >
                                    −
                                </button>
                                <span class="w-8 text-center font-bold text-slate-800">
                                    {{ item.quantity }}
                                </span>
                                <button
                                    @click="increase(item)"
                                    class="h-8 w-8 rounded-xl bg-green-500 hover:bg-green-600 transition flex items-center justify-center font-bold text-white"
                                >
                                    +
                                </button>
                            </div>

                            <button
                                @click="removeProduct(item.id)"
                                class="text-slate-300 hover:text-red-500 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-2 text-right text-sm font-semibold text-slate-600">
                            Total: {{ formatCurrency(item.price * item.quantity) }}
                        </div>
                    </div>
                </div>

                <!-- BOTÓN DE COBRAR - FIJO EN LA PARTE INFERIOR -->
                <div class="absolute bottom-0 left-0 right-0 p-6 bg-white border-t border-slate-200 rounded-b-3xl shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-slate-600">Total</span>
                        <span class="text-2xl font-bold text-green-600">{{ formatCurrency(cart.grandTotal.value) }}</span>
                    </div>

                    <button
                        @click="openPayment"
                        :disabled="cart.state.items.length === 0"
                        class="w-full py-4 rounded-2xl font-bold text-white text-lg transition-all"
                        :class="[
                            cart.state.items.length > 0
                                ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 shadow-lg shadow-green-200'
                                : 'bg-slate-300 cursor-not-allowed'
                        ]"
                    >
                        Cobrar venta
                    </button>

                    <button
                        v-if="cart.state.items.length > 0"
                        @click="clearCart"
                        class="mt-2 w-full py-2 text-sm text-slate-400 hover:text-red-500 transition text-center"
                    >
                        Limpiar carrito
                    </button>
                </div>
            </div>
        </div>

        <PaymentModal
            v-if="paymentOpen"
            :cart="cart.state.items"
            :total="cart.grandTotal.value"
            @close="paymentOpen=false"
            @success="handlePaymentSuccess"
        />
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
</style>
