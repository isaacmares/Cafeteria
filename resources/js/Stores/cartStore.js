// resources/js/Stores/cartStore.js
import { reactive, computed } from 'vue'

// Estado global del carrito
const state = reactive({
    items: [],
    customerName: '',
    customerEmail: '',
    saleId: null,
    isComplete: false,
    lastUpdate: null
})

// Guardar en localStorage
const saveToStorage = () => {
    try {
        const data = {
            items: state.items,
            customerName: state.customerName,
            customerEmail: state.customerEmail,
            saleId: state.saleId,
            isComplete: state.isComplete,
            lastUpdate: Date.now()
        }
        localStorage.setItem('cart_state', JSON.stringify(data))
    } catch (e) {
        console.error('Error saving to localStorage:', e)
    }
}

// Cargar desde localStorage
const loadFromStorage = () => {
    try {
        const saved = localStorage.getItem('cart_state')
        if (saved) {
            const data = JSON.parse(saved)
            state.items = data.items || []
            state.customerName = data.customerName || ''
            state.customerEmail = data.customerEmail || ''
            state.saleId = data.saleId || null
            state.isComplete = data.isComplete || false
            state.lastUpdate = data.lastUpdate || Date.now()
            return true
        }
    } catch (e) {
        console.error('Error loading from localStorage:', e)
    }
    return false
}

// Funciones para manipular el carrito
const addItem = (product) => {
    const existingItem = state.items.find(item => item.id === product.id)
    if (existingItem) {
        existingItem.quantity += 1
    } else {
        state.items.push({
            id: product.id,
            name: product.name,
            price: product.price,
            sku: product.sku || '',
            image: product.image || null,
            quantity: 1,
            stock: product.stock || 0
        })
    }
    state.isComplete = false
    state.lastUpdate = Date.now()
    saveToStorage()
}

const removeItem = (id) => {
    const index = state.items.findIndex(item => item.id === id)
    if (index !== -1) {
        state.items.splice(index, 1)
    }
    state.lastUpdate = Date.now()
    saveToStorage()
}

const updateQuantity = (id, quantity) => {
    const item = state.items.find(item => item.id === id)
    if (item) {
        if (quantity <= 0) {
            removeItem(id)
        } else {
            item.quantity = quantity
        }
    }
    state.lastUpdate = Date.now()
    saveToStorage()
}

const clearCart = () => {
    state.items = []
    state.customerName = ''
    state.customerEmail = ''
    state.saleId = null
    state.isComplete = false
    state.lastUpdate = Date.now()
    saveToStorage()
}

const setCustomer = (name, email) => {
    state.customerName = name
    state.customerEmail = email
    state.lastUpdate = Date.now()
    saveToStorage()
}

const setSaleId = (id) => {
    state.saleId = id
    state.lastUpdate = Date.now()
    saveToStorage()
}

const complete = () => {
    state.isComplete = true
    state.lastUpdate = Date.now()
    saveToStorage()
}

// Sincronizar estado desde localStorage
const syncFromStorage = () => {
    const saved = localStorage.getItem('cart_state')
    if (saved) {
        try {
            const data = JSON.parse(saved)
            if (data.lastUpdate && data.lastUpdate !== state.lastUpdate) {
                state.items = data.items || []
                state.customerName = data.customerName || ''
                state.customerEmail = data.customerEmail || ''
                state.saleId = data.saleId || null
                state.isComplete = data.isComplete || false
                state.lastUpdate = data.lastUpdate || Date.now()
                return true
            }
        } catch (e) {}
    }
    return false
}

// Computed properties - SIN IVA
const total = computed(() => {
    return state.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const totalItems = computed(() => {
    return state.items.reduce((sum, item) => sum + item.quantity, 0)
})

const subtotal = computed(() => total.value)

// Exportar solo el total (sin IVA)
const grandTotal = computed(() => total.value)

// Cargar estado inicial
loadFromStorage()

export const cartStore = {
    state,
    addItem,
    removeItem,
    updateQuantity,
    clearCart,
    setCustomer,
    setSaleId,
    complete,
    syncFromStorage,
    saveToStorage,
    total,
    totalItems,
    subtotal,
    grandTotal  // Esto es igual a total (sin IVA)
}
