<script setup>
// Halaman antarmuka Admin untuk mengelola pertemuan/silabus seluruh kelas secara sentralisasi.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Pencil, Trash2, X, Search } from 'lucide-vue-next';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { toast } from 'vue-sonner';

const props = defineProps({
    meetings: { type: Array, default: () => [] },
    classes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '' }) }
});

const form = useForm({
    team_id: '',
    meeting_number: '',
    title: '',
    description: '',
});
// Mengirim permintaan pembuatan modul baru ke server lalu me-reset form setelah sukses.
const submit = () => {
    form.post(route('admin.meetings.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Pembuatan Pertemuan Berhasil', {
                description: 'Pertemuan baru telah ditambahkan ke dalam kelas.',
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
    team_id: '',
    meeting_number: '',
    title: '',
    description: '',
});
// Menyalin data pertemuan yang dipilih ke dalam state form edit lokal dan memunculkan pop-up modal.
const openEditModal = (meeting) => {
    editForm.id = meeting.id;
    editForm.team_id = meeting.team_id.toString();
    editForm.meeting_number = meeting.meeting_number;
    editForm.title = meeting.title;
    editForm.description = meeting.description || '';

    isEditModalOpen.value = true;
};
// Menyembunyikan modal pengeditan pertemuan dan membersihkan isian di dalamnya.
const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
    editForm.clearErrors();
};
// Memproses update data pertemuan yang sudah diubah ke backend, kemudian menutup modal jika disetujui.
const updateSubmit = () => {
    editForm.put(route('admin.meetings.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
            toast.success('Pembaruan Berhasil', {
                description: 'Data pertemuan berhasil disimpan.',
            });
        },
        onError: () => {
            toast.error('Pembaruan Gagal', {
                description: 'Terjadi kesalahan saat menyimpan data pertemuan.',
            });
        }
    });
};

const isDeleteDialogOpen = ref(false);
const meetingToDelete = ref(null);
// Memicu trigger untuk membuka dialog alert saat user mencoba menghapus sebuah pertemuan.
const confirmDelete = (id) => {
    meetingToDelete.value = id;
    isDeleteDialogOpen.value = true;
};
// Menjalankan permintaan penghapusan (DELETE) pertemuan sesuai ID yang akan dihapus.
const executeDelete = () => {
    router.delete(route('admin.meetings.destroy', meetingToDelete.value), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            meetingToDelete.value = null;
            toast.success('Penghapusan Berhasil', {
                description: 'Pertemuan telah dihapus secara permanen.',
            });
        },
        onError: () => {
            toast.error('Penghapusan Gagal', {
                description: 'Terjadi kesalahan saat menghapus pertemuan.',
            });
        }
    });
};

const search = ref(props.filters.search);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get(route('admin.meetings.index'), { search: value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
});
</script>

<template>

    <Head title="Manajemen Pertemuan" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Manajemen Pertemuan (Modul)</h2>
        </template>

        <div class="flex flex-col xl:flex-row gap-6 relative xl:h-[calc(100vh-8.5rem)]">

            <div
                class="bg-white rounded-xl p-6 w-full xl:w-1/3 h-fit xl:max-h-full overflow-y-auto border border-slate-200 shadow-sm custom-scrollbar">
                <h3 class="text-lg font-bold mb-4 border-b border-slate-100 pb-3">Buat Pertemuan Baru</h3>
                <form @submit.prevent="submit" class="space-y-4">

                    <div class="space-y-2">
                        <Label for="team_id">Pilih Kelas</Label>
                        <Select v-model="form.team_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="kelas in classes" :key="kelas.id" :value="kelas.id.toString()">
                                    {{ kelas.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.team_id" class="text-sm text-red-500">{{ form.errors.team_id }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1 space-y-2">
                            <Label for="meeting_number">Pert. Ke</Label>
                            <Input class="rounded-lg" id="meeting_number" type="number" min="1" max="16"
                                v-model="form.meeting_number" required />
                            <p v-if="form.errors.meeting_number" class="text-sm text-red-500">{{
                                form.errors.meeting_number }}
                            </p>
                        </div>
                        <div class="col-span-2 space-y-2">
                            <Label for="title">Judul Modul</Label>
                            <Input class="rounded-lg" id="title" type="text" v-model="form.title"
                                placeholder="Contoh: Pengenalan Vue" required />
                            <p v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Deskripsi Singkat</Label>
                        <textarea id="description" v-model="form.description" rows="3"
                            class="flex min-h-[80px] w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Opsional: Tuliskan capaian pembelajaran..."></textarea>
                        <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>

                    <div class="pt-4">
                        <Button type="submit" class="w-full bg-[#194872] hover:bg-[#194872]/80 text-white"
                            :disabled="form.processing">
                            Tambahkan Pertemuan
                        </Button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white rounded-xl w-full xl:w-2/3 border border-slate-200 shadow-sm flex flex-col xl:h-full overflow-hidden p-6">

                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0 gap-4">
                    <h3 class="text-lg font-bold">Daftar Pertemuan</h3>
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input v-model="search" type="text" placeholder="Cari judul, kelas, atau pertemuan ke-..."
                            class="pl-9 bg-slate-50 border-slate-200 focus-visible:ring-slate-300" />
                    </div>
                </div>

                <div class="border border-slate-200 rounded-lg flex-1 overflow-y-auto relative custom-scrollbar">
                    <Table>
                        <TableHeader class="bg-slate-50 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                            <TableRow>
                                <TableHead class="font-semibold text-slate-700 py-3 px-4 w-16 text-center">Ke-
                                </TableHead>
                                <TableHead class="font-semibold text-slate-700 py-3 px-4">Judul Modul</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-3 px-4">Nama Kelas</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-3 px-4 text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="meetings.length === 0">
                                <TableCell colspan="4" class="text-center text-slate-500 h-32">
                                    Belum ada data pertemuan.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="meeting in meetings" :key="meeting.id">
                                <TableCell class="py-3 px-4 text-center font-bold text-[#194872]">
                                    {{ meeting.meeting_number }}
                                </TableCell>
                                <TableCell class="py-3 px-4">
                                    <div class="font-medium text-slate-900">{{ meeting.title }}</div>
                                    <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ meeting.description ||
                                        'Tidak ada deskripsi' }}</div>
                                </TableCell>
                                <TableCell class="py-3 px-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-amber-100 text-amber-700">
                                        {{ meeting.team?.name || 'Kelas Terhapus' }}
                                    </span>
                                </TableCell>
                                <TableCell class="py-3 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-[#194872] border-[#194872] hover:bg-[#194872]/10"
                                            @click="openEditModal(meeting)">
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50"
                                            @click="confirmDelete(meeting.id)">
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
                    <h3 class="text-lg font-bold text-slate-800">Edit Pertemuan</h3>
                    <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="updateSubmit" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Label for="edit_team_id">Kelas</Label>
                        <Select v-model="editForm.team_id" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="kelas in classes" :key="kelas.id" :value="kelas.id.toString()">
                                    {{ kelas.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="editForm.errors.team_id" class="text-sm text-red-500">{{ editForm.errors.team_id }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1 space-y-2">
                            <Label for="edit_meeting_number">Pert. Ke</Label>
                            <Input class="rounded-lg" id="edit_meeting_number" type="number" min="1" max="16"
                                v-model="editForm.meeting_number" required />
                        </div>
                        <div class="col-span-2 space-y-2">
                            <Label for="edit_title">Judul Modul</Label>
                            <Input class="rounded-lg" id="edit_title" type="text" v-model="editForm.title" required />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit_description">Deskripsi Singkat</Label>
                        <textarea id="edit_description" v-model="editForm.description" rows="3"
                            class="flex min-h-[80px] w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
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
                    <AlertDialogTitle>Hapus Pertemuan?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini permanen dan tidak dapat dibatalkan. Data pertemuan beserta seluruh materi di
                        dalamnya akan
                        dihapus dari kelas.
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