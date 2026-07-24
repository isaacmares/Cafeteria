<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    LayoutDashboard,
    ShoppingCart,
    Package,
    Tags,
    Boxes,
    Users,
    BarChart3,
    Settings,
    Coffee,
    Menu,
    ChevronLeft,
    LogOut
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)
const sidebarOpen = ref(false)

const menu = [
    {
        title: 'Principal',
        items: [
            { name: 'Dashboard', url: '/dashboard', icon: LayoutDashboard },
            { name: 'Ventas', url: '/sales', icon: ShoppingCart },
            { name: 'Caja', url: '/cash-register', icon: LayoutDashboard },
        ]
    },
    {
        title: 'Catálogo',
        items: [
            { name: 'Productos', url: '/products', icon: Package },
            { name: 'Categorías', url: '/categories', icon: Tags },
            { name: 'Inventario', url: '/inventory', icon: Boxes },
        ]
    },
    {
        title: 'Administración',
        items: [
            { name: 'Clientes', url: '/customers', icon: Users },
            { name: 'Reportes', url: '/reports', icon: BarChart3 },
            { name: 'Configuración', url: '/settings', icon: Settings },
        ]
    }
]
</script>

<template>
    <div class="min-h-screen bg-[#faf9f7]">

        <!-- SIDEBAR -->
        <aside
            :class="[
                sidebarOpen ? 'w-72' : 'w-24',
                'fixed top-4 left-4 bottom-4 z-50 rounded-3xl bg-white shadow-xl shadow-slate-200/50 border border-slate-100 transition-all duration-300'
            ]"
        >
            <div class="h-full flex flex-col">

                <!-- LOGO -->
                <div class="p-5 flex items-center gap-3">
                    <div
                        class="
                            h-12 w-12 rounded-2xl
                            bg-gradient-to-br from-green-500 to-green-700
                            flex items-center justify-center
                            text-white shadow-lg
                        "
                    >
                        <Coffee size="25" />
                    </div>

                    <div v-if="sidebarOpen">
                        <h1 class="font-black text-xl text-slate-900">
                            Cielo
                        </h1>
                        <p class="text-xs text-slate-400">
                            Coffee POS
                        </p>
                    </div>
                </div>

                <div class="px-4">
                    <div class="h-px bg-slate-100" />
                </div>

                <!-- MENU -->
                <nav class="flex-1 px-4 py-6 overflow-y-auto">
                    <div
                        v-for="section in menu"
                        :key="section.title"
                        class="mb-8"
                    >
                        <p
                            v-if="sidebarOpen"
                            class="
                                text-[11px]
                                font-bold
                                uppercase
                                tracking-widest
                                text-slate-400
                                mb-3
                                px-3
                            "
                        >
                            {{ section.title }}
                        </p>

                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="item.url"
                                class="
                                    group
                                    flex items-center
                                    gap-3
                                    rounded-2xl
                                    px-3 py-3
                                    text-sm
                                    font-semibold
                                    text-slate-500
                                    hover:bg-green-50
                                    hover:text-green-700
                                    transition-all
                                "
                            >
                                <div
                                    class="
                                        h-10 w-10
                                        rounded-xl
                                        flex items-center justify-center
                                        bg-slate-50
                                        group-hover:bg-green-100
                                        transition
                                    "
                                >
                                    <component
                                        :is="item.icon"
                                        size="20"
                                    />
                                </div>

                                <span v-if="sidebarOpen">
                                    {{ item.name }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- USER -->
                <div class="p-4">
                    <div
                        class="
                            rounded-2xl
                            bg-slate-50
                            p-3
                            flex items-center
                            gap-3
                        "
                    >
                        <div
                            class="
                                h-11 w-11
                                rounded-full
                                bg-gradient-to-br
                                from-green-500
                                to-green-700
                                flex items-center
                                justify-center
                                text-white
                                font-bold
                            "
                        >
                            {{ user?.name?.charAt(0) }}
                        </div>

                        <div
                            v-if="sidebarOpen"
                            class="overflow-hidden"
                        >
                            <p class="text-sm font-bold truncate">
                                {{ user?.name }}
                            </p>
                            <p class="text-xs text-slate-400">
                                Administrador
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="
                            mt-3
                            w-full
                            flex items-center
                            justify-center
                            gap-2
                            rounded-xl
                            py-2
                            text-sm
                            font-semibold
                            text-slate-500
                            hover:bg-red-50
                            hover:text-red-600
                            transition
                        "
                    >
                        <LogOut size="16" />
                        <span v-if="sidebarOpen">
                            Salir
                        </span>
                    </Link>
                </div>

            </div>
        </aside>

        <!-- CONTENT -->
        <div
            :class="[
                sidebarOpen ? 'ml-80' : 'ml-32',
                'transition-all duration-300'
            ]"
        >
            <header
                class="
                    sticky top-0
                    z-40
                    h-20
                    flex items-center justify-between
                    px-8
                    bg-[#faf9f7]/80
                    backdrop-blur-xl
                "
            >
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="
                        h-11 w-11
                        rounded-2xl
                        bg-white
                        shadow-sm
                        border
                        border-slate-100
                        flex
                        items-center
                        justify-center
                        hover:scale-105
                        transition
                    "
                >
                    <Menu size="20" />
                </button>

                <div
                    class="
                        bg-white
                        rounded-2xl
                        px-5 py-3
                        shadow-sm
                        border border-slate-100
                    "
                >
                    <p class="text-sm font-bold text-slate-800">
                        Cielo Coffee
                    </p>
                    <p class="text-xs text-slate-400">
                        Sistema POS
                    </p>
                </div>
            </header>

            <main class="p-8">
                <slot />
            </main>
        </div>

    </div>
</template>
