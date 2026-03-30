<script setup>
// Halaman beranda utama Mahasiswa yang menampilkan grid kartu kelas yang mereka ikuti beserta form join.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { BookOpen, Plus, X, GraduationCap, ArrowRight, Clock, CheckCircle2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    teams: { type: Array, default: () => [] }
});

const isJoinModalOpen = ref(false);

const form = useForm({
    join_code: '',
});
// Memproses input string 6-karakter (join code) ke server untuk mendaftarkan mahasiswa ke kelas terkait.
const submitJoin = () => {
    form.post(route('mahasiswa.join'), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            isJoinModalOpen.value = false;
            form.reset();
            toast.success('Permintaan Terkirim!', {
                description: 'Berhasil meminta bergabung. Silakan tunggu persetujuan dari Dosen.',
            });
        },
        onError: () => {
            toast.error('Gagal Bergabung', {
                description: form.errors.join_code || 'Terjadi kesalahan saat memeriksa kode kelas.',
            });
        }
    });
};
</script>

<template>

    <Head title="Beranda Mahasiswa - Kelas Saya" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-slate-800 leading-tight">Kelas Pembelajaran</h2>
                    <p class="text-sm text-slate-500">Daftar kelas yang Anda ikuti saat ini.</p>
                </div>
                <Button @click="isJoinModalOpen = true"
                    class="bg-[#194872] hover:bg-[#194872]/90 text-white flex items-center gap-2 shadow-sm">
                    <Plus class="w-4 h-4" /> Gabung Kelas Baru
                </Button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-12">

            <div v-if="teams.length === 0"
                class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center flex flex-col items-center justify-center min-h-[400px]">
                <div class="bg-blue-50 p-6 rounded-full text-[#194872] mb-4">
                    <GraduationCap class="w-12 h-12" />
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Belum Mengikuti Kelas</h3>
                <p class="text-slate-500 max-w-md mb-6">Anda belum terdaftar di kelas manapun. Mintalah kode akses dari
                    Dosen
                    Anda untuk mulai belajar.</p>
                <Button @click="isJoinModalOpen = true" class="bg-[#194872] hover:bg-[#194872]/90 text-white">
                    Masukkan Kode Kelas
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="kelas in teams" :key="kelas.id"
                    class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">

                    <div class="h-24 bg-[#194872] p-6 relative">
                        <h3 class="text-lg font-bold text-white truncate pr-8">{{ kelas.name }}</h3>
                        <div class="absolute top-6 right-6 text-white/20">
                            <BookOpen class="w-12 h-12" />
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-center">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Status Kepesertaan
                        </p>

                        <div v-if="kelas.membership.status === 'pending'"
                            class="flex items-center gap-2 text-orange-700 bg-orange-50 px-4 py-2.5 rounded-lg text-sm font-medium border border-orange-200 w-fit">
                            <Clock class="w-4 h-4 shrink-0" /> Menunggu Persetujuan
                        </div>

                        <div v-else
                            class="flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2.5 rounded-lg text-sm font-medium border border-green-200 w-fit">
                            <CheckCircle2 class="w-4 h-4 shrink-0" /> Terdaftar Aktif
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex">
                        <Link v-if="kelas.membership.status === 'approved'" :href="route('mahasiswa.classes.show', kelas.id)"
                            class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium bg-[#194872] text-white hover:bg-[#194872]/90 transition-colors h-10 shadow-sm">
                            Masuk Kelas
                            <ArrowRight class="w-4 h-4 ml-2" />
                        </Link>

                        <button v-else disabled
                            class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-200 text-slate-500 cursor-not-allowed h-10">
                            Belum Bisa Diakses
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <div v-if="isJoinModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
            <div
                class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Gabung Kelas Baru</h3>
                    <button @click="isJoinModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitJoin" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Label for="join_code">Kode Akses Kelas</Label>
                        <Input id="join_code" type="text" v-model="form.join_code" placeholder="Contoh: X7Y8Z9"
                            class="font-mono text-lg tracking-widest uppercase rounded-lg" required />
                        <p class="text-xs text-slate-500 mt-1">Mintalah 6 digit kode akses ini kepada Dosen pengampu
                            Anda.</p>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="isJoinModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white"
                            :disabled="form.processing">
                            Gabung Sekarang
                        </Button>
                    </div>
                </form>
            </div>
        </div>

    </AdminLayout>
</template>