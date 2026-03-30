<script setup>
// Halaman Admin untuk CRUD dan manajemen data semua kelas/mata kuliah di sistem.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Pencil, Trash2, X, Search, Copy } from 'lucide-vue-next';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { toast } from 'vue-sonner';

const props = defineProps({
    classes: { type: Array, default: () => [] },
    dosens: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '' }) }
});

const form = useForm({
    name: '',
    user_id: '',
});
// Mengirim data form ke server untuk menyimpan entitas Kelas baru.
const submit = () => {
    form.post(route('admin.classes.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Pembuatan Kelas Berhasil', {
                description: 'Kelas baru telah ditambahkan ke dalam sistem.',
            });
        },
        onError: () => {
            toast.error('Pembuatan Gagal', {
                description: 'Silakan periksa kembali isian form Anda.',
            });
        }
    });
};

const isEditModalOpen = ref(false);

const editForm = useForm({
    id: '',
    name: '',
    user_id: '',
});
// Membuka jendela modal dan mengisi form sesuai spesifik kelas yang dipilih untuk diedit.
const openEditModal = (kelas) => {
    editForm.id = kelas.id;
    editForm.name = kelas.name;
    editForm.user_id = kelas.user_id.toString();

    isEditModalOpen.value = true;
};
// Menutup jendela modal edit serta membersihkan sisa input di dalam form.
const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
    editForm.clearErrors();
};
// Menyimpan data pembaruan untuk kelas spesifik ke dalam sistem.
const updateSubmit = () => {
    editForm.put(route('admin.classes.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
            toast.success('Pembaruan Berhasil', {
                description: 'Data kelas berhasil disimpan.',
            });
        },
        onError: () => {
            toast.error('Pembaruan Gagal', {
                description: 'Terjadi kesalahan saat menyimpan data kelas.',
            });
        }
    });
};

const isDeleteDialogOpen = ref(false);
const classToDelete = ref(null);
// Membuka modal dialog konfirmasi penghapusan kelas spesifik.
const confirmDelete = (id) => {
    classToDelete.value = id;
    isDeleteDialogOpen.value = true;
};
// Mengeksekusi request DELETE ke server untuk menghapus kelas dari database.
const executeDelete = () => {
    router.delete(route('admin.classes.destroy', classToDelete.value), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            classToDelete.value = null;
            toast.success('Penghapusan Berhasil', {
                description: 'Kelas beserta seluruh isinya telah dihapus secara permanen.',
            });
        },
        onError: () => {
            toast.error('Penghapusan Gagal', {
                description: 'Terjadi kesalahan saat menghapus kelas.',
            });
        }
    });
};
// Menyalin join code (kode masuk) kelas ke papan klip desktop/device pengguna.
const copyJoinCode = (code) => {
    navigator.clipboard.writeText(code);
    toast.info('Tersalin!', {
        description: `Kode akses ${code} disalin ke clipboard.`,
    });
};

const search = ref(props.filters.search);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get(route('admin.classes.index'), { search: value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
});
</script>

<template>

    <Head title="Manajemen Kelas" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Kelas Manajemen</h2>
        </template>

        <div class="flex flex-col xl:flex-row gap-6 relative xl:h-[calc(100vh-8.5rem)]">

            <div
                class="bg-white rounded-xl p-6 w-full xl:w-1/3 h-fit xl:max-h-full overflow-y-auto border border-slate-200 shadow-sm custom-scrollbar">
                <h3 class="text-lg font-bold mb-4 border-b border-slate-100 pb-3">Buat Kelas Baru</h3>
                <form @submit.prevent="submit" class="space-y-4">

                    <div class="space-y-2">
                        <Label for="name">Nama Kelas (Mata Kuliah)</Label>
                        <Input class="rounded-lg" id="name" type="text" v-model="form.name"
                            placeholder="Contoh: Pemrograman Web Lanjut" required />
                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="user_id">Dosen Pengampu</Label>
                        <Select v-model="form.user_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Dosen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="dosen.id.toString()">
                                    {{ dosen.username }} {{ dosen.nim_nip ? `(${dosen.nim_nip})` : '' }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.user_id" class="text-sm text-red-500">{{ form.errors.user_id }}</p>
                    </div>

                    <div class="pt-4">
                        <Button type="submit" class="w-full bg-[#194872] hover:bg-[#194872]/80 text-white"
                            :disabled="form.processing">
                            Buat Kelas
                        </Button>
                    </div>

                    <div class="mt-4 p-4 bg-blue-50 text-blue-800 text-sm rounded-lg">
                        <strong>Info:</strong> Kode Akses bergabung (Join Code) untuk Mahasiswa akan digenerate otomatis
                        oleh
                        sistem setelah kelas dibuat.
                    </div>
                </form>
            </div>

            <div
                class="bg-white rounded-xl w-full xl:w-2/3 border border-slate-200 shadow-sm flex flex-col xl:h-full overflow-hidden p-6">

                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0 gap-4">
                    <h3 class="text-lg font-bold">Daftar Kelas</h3>
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input v-model="search" type="text" placeholder="Cari nama kelas atau kode..."
                            class="pl-9 bg-slate-50 border-slate-200 focus-visible:ring-slate-300" />
                    </div>
                </div>

                <div class="border border-slate-200 rounded-lg flex-1 overflow-y-auto relative custom-scrollbar">
                    <Table>
                        <TableHeader class="bg-slate-50 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                            <TableRow>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">Nama Kelas</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">Dosen</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4 text-center">Kode Akses
                                </TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4 text-center">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="classes.length === 0">
                                <TableCell colspan="4" class="text-center text-slate-500 h-32">
                                    Belum ada data kelas.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="kelas in classes" :key="kelas.id">
                                <TableCell class="py-2 px-4">
                                    <div class="font-medium text-slate-900">{{ kelas.name }}</div>
                                    <div class="text-[11px] text-slate-500">Dibuat: {{ new
                                        Date(kelas.created_at).toLocaleDateString('id-ID') }}</div>
                                </TableCell>
                                <TableCell class="py-2 px-4 text-slate-600 capitalize">
                                    {{ kelas.owner ? kelas.owner.username : 'Tidak diketahui' }}
                                </TableCell>
                                <TableCell class="py-2 px-4 text-center">
                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 border border-slate-200 rounded-md">
                                        <span class="font-mono font-bold text-slate-800 tracking-wider">{{
                                            kelas.join_code
                                            }}</span>
                                        <button @click="copyJoinCode(kelas.join_code)"
                                            class="text-slate-400 hover:text-blue-600 transition-colors"
                                            title="Salin Kode">
                                            <Copy class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </TableCell>
                                <TableCell class="py-2 px-4">
                                    <div class="flex justify-center gap-2">
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-[#194872] border-[#194872] hover:bg-[#194872]/10"
                                            @click="openEditModal(kelas)">
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50"
                                            @click="confirmDelete(kelas.id)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>

        <div v-if="isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
            <div
                class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Edit Kelas</h3>
                    <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="updateSubmit" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Label for="edit_name">Nama Kelas (Mata Kuliah)</Label>
                        <Input class="rounded-lg" id="edit_name" type="text" v-model="editForm.name" required />
                        <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit_user_id">Dosen Pengampu</Label>
                        <Select v-model="editForm.user_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Dosen" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="dosen.id.toString()">
                                    {{ dosen.username }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="editForm.errors.user_id" class="text-sm text-red-500">{{ editForm.errors.user_id }}</p>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="closeEditModal">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/80 text-white"
                            :disabled="editForm.processing">
                            Simpan Perubahan
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <AlertDialogContent class="z-[110]">
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Kelas?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini permanen dan tidak dapat dibatalkan. Kelas ini beserta seluruh data pertemuan dan
                        anggota
                        di dalamnya akan dihapus.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isDeleteDialogOpen = false">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="executeDelete" class="bg-red-600 hover:bg-red-700 text-white">
                        Ya, Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>