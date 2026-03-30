<script setup>
// Halaman dashboard bagi entitas Dosen yang memuat grid berisi daftar kelas-kelas milik mereka.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { BookOpen, Copy, Plus, Settings, Eye, Users, X } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    classes: { type: Array, default: () => [] }
});

// Fungsi utilitas untuk menyalin teks random 'join code' 6 karakter demi mengundang mahasiswa.
const copyJoinCode = (code) => {
    navigator.clipboard.writeText(code);
    toast.info('Kode Tersalin!', {
        description: `Kode akses ${code} siap dibagikan ke mahasiswa.`,
    });
};

const isCreateModalOpen = ref(false);

const form = useForm({
    name: '',
});
// Mengirim trigger permintaan POST untuk membuat sebuah ruang kelas/mata kuliah baru.
const submit = () => {
    form.post(route('dosen.classes.store'), {
        onSuccess: () => {
            form.reset();
            isCreateModalOpen.value = false;
            toast.success('Kelas Berhasil Dibuat', {
                description: 'Anda sekarang dapat mengelola kelas dan mengunggah materi.',
            });
        },
    });
};
</script>

<template>

    <Head title="Dashboard Dosen - Kelas Saya" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Kelas Saya</h2>
                <Button @click="isCreateModalOpen = true"
                    class="bg-[#194872] hover:bg-[#194872]/80 text-white flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Buat Kelas Baru
                </Button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto">

            <div v-if="classes.length === 0"
                class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center flex flex-col items-center justify-center min-h-[400px]">
                <div class="bg-blue-50 p-6 rounded-full text-[#194872] mb-4">
                    <BookOpen class="w-12 h-12" />
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Belum Ada Kelas</h3>
                <p class="text-slate-500 max-w-md mb-6">Anda belum memiliki kelas yang diampu. Silakan buat kelas baru
                    untuk
                    memulai.</p>
                <Button @click="isCreateModalOpen = true" class="bg-[#194872] hover:bg-[#194872]/80 text-white">
                    Buat Kelas Pertama
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="kelas in classes" :key="kelas.id"
                    class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">

                    <div class="h-24 bg-[#194872] p-6 relative">
                        <h3 class="text-lg font-bold text-white truncate pr-8">{{ kelas.name }}</h3>
                        <p class="text-blue-100 text-sm mt-1">Dibuat: {{ new
                            Date(kelas.created_at).toLocaleDateString('id-ID')
                            }}</p>
                        <div class="absolute top-6 right-6 text-white/20">
                            <BookOpen class="w-12 h-12" />
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-center">
                        <div class="mb-2 text-sm font-medium text-slate-500 uppercase tracking-wider">Kode Akses Kelas
                        </div>
                        <div
                            class="flex items-center justify-between bg-slate-50 border border-slate-200 p-3 rounded-lg">
                            <span class="font-mono font-bold text-xl text-slate-800 tracking-widest">{{ kelas.join_code
                                }}</span>
                            <Button variant="ghost" size="icon" @click="copyJoinCode(kelas.join_code)"
                                class="text-slate-500 hover:text-[#194872] hover:bg-blue-50">
                                <Copy class="w-5 h-5" />
                            </Button>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex gap-3">
                        <Link :href="route('dosen.classes.show', kelas.id)"
                            class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-[#194872] transition-colors h-10">
                            <Eye class="w-4 h-4 mr-2" /> Lihat Kelas
                        </Link>
                        <Link :href="route('dosen.classes.manage', kelas.id)"
                            class="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium bg-[#194872] text-white hover:bg-[#194872]/90 transition-colors h-10 shadow-sm">
                            <Settings class="w-4 h-4 mr-2" /> Edit Kelas
                        </Link>
                    </div>

                </div>
            </div>

        </div>

        <div v-if="isCreateModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
            <div
                class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Buat Kelas Baru</h3>
                    <button @click="isCreateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Kelas (Mata Kuliah)</Label>
                        <Input class="rounded-lg" id="name" type="text" v-model="form.name" required />
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="isCreateModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/80 text-white"
                            :disabled="form.processing">
                            Buat Kelas
                        </Button>
                    </div>
                </form>
            </div>
        </div>

    </AdminLayout>
</template>