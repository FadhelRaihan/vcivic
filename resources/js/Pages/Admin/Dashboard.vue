<script setup>
// Halaman tampilan (UI) utama dari Dashboard untuk melihat ringkasan statistik Admin.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Users, GraduationCap, BookOpen } from 'lucide-vue-next';

defineProps({
    stats: {
        type: Object,
        default: () => ({ mahasiswa: 0, dosen: 0, kelas: 0 })
    }
});
</script>

<template>

    <Head title="Dashboard Admin" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Dashboard Admin VCivic</h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex items-center gap-4">
                <div class="bg-blue-100 p-4 rounded-lg text-blue-600">
                    <Users class="w-8 h-8" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Mahasiswa</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ stats.mahasiswa }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex items-center gap-4">
                <div class="bg-green-100 p-4 rounded-lg text-green-600">
                    <GraduationCap class="w-8 h-8" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Dosen</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ stats.dosen }}</h3>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex items-center gap-4">
                <div class="bg-amber-100 p-4 rounded-lg text-amber-600">
                    <BookOpen class="w-8 h-8" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Kelas Aktif</p>
                    <h3 class="text-3xl font-bold text-slate-800">{{ stats.kelas }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8 text-center">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Selamat Datang di Portal Admin VCivic!</h2>
            <p class="text-slate-500">Gunakan menu di sebelah kiri untuk mengelola Pengguna, Kelas, dan Modul Pertemuan.
            </p>
        </div>

    </AdminLayout>
</template>