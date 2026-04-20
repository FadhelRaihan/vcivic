<script setup>
// File Layout Utama khusus untuk kerangka halaman Dashboard Admin.
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel,
    DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    Menu, LogOut, ChevronDown, ShieldAlert, GraduationCap, BookOpen,
    LayoutDashboard, Users, Presentation, X, Database
} from 'lucide-vue-next';
import 'vue-sonner/style.css'
import { Toaster } from '@/Components/ui/sonner'

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(false);
// Mengeluarkan user admin dari sistem menggunakan rute logout bawaan Fortify.
const logout = () => {
    sessionStorage.removeItem('motivation_shown');
    router.post(route('logout'));
};

// Navigasi Menu Admin
const adminMenus = [
    { name: 'Dashboard', href: route('admin.dashboard'), icon: LayoutDashboard, active: route().current('admin.dashboard') },
    { name: 'Kelola Pengguna', href: route('admin.users.index'), icon: Users, active: route().current('admin.users.*') },
    { name: 'Master Kurikulum', href: route('admin.curriculum.manage'), icon: Database, active: route().current('admin.curriculum.*') },
    { name: 'Kelola Kelas', href: route('admin.classes.index'), icon: BookOpen, active: route().current('admin.classes.*') },
    { name: 'Kelola Pertemuan', href: route('admin.meetings.index'), icon: Presentation, active: route().current('admin.meetings.*') },
];
</script>

<template>
    <div class="flex h-screen bg-slate-50 overflow-hidden">

        <div v-if="isSidebarOpen && user.role === 'admin'" @click="isSidebarOpen = false"
            class="fixed inset-0 bg-slate-900/50 z-40 md:hidden">
        </div>

        <aside v-if="user.role === 'admin'" :class="[
            'fixed inset-y-0 left-0 z-50 w-64 bg-white text-slate-50 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:flex md:flex-col',
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]">
            <div class="h-16 flex items-center justify-between px-6 border-b border-r border-slate-200 shrink-0">
                <Link :href="route('dashboard')" class="">
                    <img src="/Logo.svg" alt="logo" class="w-20">
                </Link>
                <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 border-r border-slate-200">
                <div class="text-xs font-semibold text-slate-800 uppercase tracking-wider mb-4 px-2">Menu Utama</div>

                <Link v-for="menu in adminMenus" :key="menu.name" :href="menu.href" :class="[
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors duration-200',
                    menu.active ? 'bg-[#194872] text-white font-medium shadow-md' : 'text-slate-800 hover:bg-[#194872]/80 hover:text-white'
                ]">
                    <component :is="menu.icon" class="w-5 h-5 shrink-0" />
                    <span>{{ menu.name }}</span>
                </Link>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0 z-30 shadow-sm">

                <div class="flex items-center gap-4">
                    <Button v-if="user.role === 'admin'" variant="ghost" size="icon" @click="isSidebarOpen = true"
                        class="md:hidden text-slate-600 -ml-2">
                        <Menu class="w-5 h-5" />
                    </Button>

                    <Link v-if="user.role !== 'admin'" :href="route('dashboard')" class="mr-2 md:mr-6">
                        <img src="/Logo.svg" alt="logo" class="w-20">
                    </Link>

                    <div class="hidden sm:block">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center mr-2">
                        <span v-if="user.role === 'admin'"
                            class="flex items-center text-xs font-bold px-2.5 py-1 bg-red-100 text-red-700 rounded-md">
                            <ShieldAlert class="w-3.5 h-3.5 mr-1" /> ADMIN
                        </span>
                        <span v-else-if="user.role === 'dosen'"
                            class="flex items-center text-xs font-bold px-2.5 py-1 bg-blue-100 text-blue-700 rounded-md">
                            <GraduationCap class="w-3.5 h-3.5 mr-1" /> DOSEN
                        </span>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost"
                                class="flex items-center gap-3 hover:bg-slate-100 h-10 px-2 rounded-lg">
                                <div
                                    class="w-8 h-8 rounded-full bg-[#194872] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ user?.username?.charAt(0) || 'U' }}
                                </div>
                                <div class="hidden md:flex flex-col items-start leading-tight">
                                    <span class="font-semibold text-sm text-slate-800 capitalize">{{ user?.username ||
                                        'User' }}</span>
                                    <span class="text-xs text-slate-500">{{ user.role.toUpperCase() }}</span>
                                </div>
                                <ChevronDown class="w-4 h-4 text-slate-400 hidden md:block" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56 mt-1">
                            <DropdownMenuLabel>Akun Saya</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <div class="px-2 py-2 text-sm text-slate-500 break-words">{{ user.email }}</div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="logout"
                                class="text-red-600 font-medium cursor-pointer focus:bg-red-50 focus:text-red-700">
                                <LogOut class="mr-2 w-4 h-4" />
                                <span>Keluar dari VCivic</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div class="mb-6 sm:hidden">
                    <slot name="header" />
                </div>

                <slot />
            </main>

        </div>

        <Toaster position="top-center" richColors="true" />
    </div>
</template>