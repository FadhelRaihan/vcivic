<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Menu, LogOut, ChevronDown, ShieldAlert, GraduationCap, BookOpen } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;

const isMobileMenuOpen = ref(false);
const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    
                    <div class="flex items-center gap-8">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-2xl font-black text-blue-700 tracking-tighter">
                                VCivic<span class="text-amber-500">.</span>
                            </Link>
                        </div>
                        
                        <div class="hidden sm:flex items-center">
                            <span v-if="user.role === 'admin'" class="flex items-center text-xs font-bold px-2 py-1 bg-red-100 text-red-700 rounded-md">
                                <ShieldAlert class="w-3 h-3 mr-1" /> ADMIN
                            </span>
                            <span v-else-if="user.role === 'dosen'" class="flex items-center text-xs font-bold px-2 py-1 bg-blue-100 text-blue-700 rounded-md">
                                <GraduationCap class="w-3 h-3 mr-1" /> DOSEN
                            </span>
                            <span v-else class="flex items-center text-xs font-bold px-2 py-1 bg-green-100 text-green-700 rounded-md">
                                <BookOpen class="w-3 h-3 mr-1" /> MAHASISWA
                            </span>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" class="flex items-center gap-2 hover:bg-slate-100">
                                    <div class="flex flex-col items-end leading-tight">
                                        <span class="font-semibold text-sm text-slate-800">{{ user.name }}</span>
                                        <span class="text-xs text-slate-500">{{ user.nim_nip }}</span>
                                    </div>
                                    <ChevronDown class="w-4 h-4 text-slate-500" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuLabel>Akun Saya</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <div class="px-2 py-2 text-sm text-slate-500 break-words">
                                    {{ user.email }}
                                </div>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="logout" class="text-red-600 font-medium cursor-pointer focus:bg-red-50 focus:text-red-700">
                                    <LogOut class="mr-2 w-4 h-4" />
                                    <span>Keluar dari VCivic</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="flex items-center sm:hidden">
                        <Button variant="ghost" size="icon" @click="isMobileMenuOpen = !isMobileMenuOpen">
                            <Menu class="w-5 h-5 text-slate-600" />
                        </Button>
                    </div>
                </div>
            </div>

            <div v-show="isMobileMenuOpen" class="sm:hidden border-t border-slate-200 bg-white">
                <div class="pt-4 pb-3 border-t border-slate-200">
                    <div class="px-4 mb-3">
                        <div class="font-bold text-base text-slate-800">{{ user.name }}</div>
                        <div class="font-medium text-sm text-slate-500">{{ user.email }}</div>
                    </div>
                    <div class="space-y-1">
                        <button @click="logout" class="flex w-full items-center px-4 py-3 text-base font-medium text-red-600 hover:bg-red-50">
                            <LogOut class="mr-3 w-5 h-5" /> Keluar
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white border-b border-slate-200 shadow-sm" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="min-h-[calc(100vh-4rem)]">
            <slot />
        </main>

    </div>
</template>