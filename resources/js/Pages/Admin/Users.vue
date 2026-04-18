<script setup>
// Halaman antarmuka Admin untuk penambahan, pengeditan, atau penghapusan akun Dosen dan Mahasiswa.
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
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ search: '' }) }
});

const form = useForm({
    name: '', email: '', password: '', role: 'mahasiswa', nim_nip: '',
});
// Mendaftarkan akun user baru (Mahasiswa/Dosen/Admin) melalui request ke server sentral.
const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Pendaftaran Berhasil', {
                description: 'Pengguna baru telah ditambahkan ke dalam sistem.',
            });
        },
        onError: () => {
            toast.error('Pendaftaran Gagal', {
                description: 'Terjadi kesalahan saat menambahkan pengguna.',
            });
        }
    });
};

const isEditModalOpen = ref(false);

const editForm = useForm({
    id: '', name: '', email: '', password: '', role: '', nim_nip: '',
});
// Membuka UI modal form edit dan mem-populate data pengguna yang diklik.
const openEditModal = (user) => {
    editForm.id = user.id;
    editForm.name = user.username;
    editForm.email = user.email;
    editForm.nim_nip = user.nim_nip;
    editForm.role = user.role;
    editForm.password = '';

    isEditModalOpen.value = true;
};
// Menutup layar modal edit user dan menghapus state input sementara.
const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
    editForm.clearErrors();
};
// Menyimpan modifikasi atribut user ke dalam database sistem admin.
const updateSubmit = () => {
    editForm.put(route('admin.users.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
            toast.success('Pembaruan Berhasil', {
                description: 'Data pengguna berhasil disimpan.',
            });
        },
        onError: () => {
            toast.error('Pembaruan Gagal', {
                description: 'Terjadi kesalahan saat menyimpan data pengguna.',
            });
        }
    });
};

const isDeleteDialogOpen = ref(false);
const userToDelete = ref(null);
// Menampilkan modal konfirmasi dengan menyimpan ID pengguna sementara ke dalam referensi.
const confirmDelete = (id) => {
    userToDelete.value = id;
    isDeleteDialogOpen.value = true;
};
// Menghapus data akun target secara permanen melalui request DELETE ke rute kontroler Admin.
const executeDelete = () => {
    router.delete(route('admin.users.destroy', userToDelete.value), {
        onSuccess: () => {
            isDeleteDialogOpen.value = false;
            userToDelete.value = null;
            toast.success('Penghapusan Berhasil', {
                description: 'Pengguna telah dihapus secara permanen.',
            });
        },
        onError: () => {
            toast.error('Penghapusan Gagal', {
                description: 'Terjadi kesalahan saat menghapus pengguna.',
            });
        }
    });
};

const search = ref(props.filters.search);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), { search: value }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
});
</script>

<template>

    <Head title="Kelola Pengguna" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">User Manajemen</h2>
        </template>

        <div class="flex flex-col xl:flex-row gap-6 relative xl:h-[calc(100vh-8.5rem)]">

            <div
                class="bg-white rounded-xl p-6 w-full xl:w-1/3 h-fit xl:max-h-full overflow-y-auto border border-slate-200 shadow-sm custom-scrollbar">
                <h3 class="text-lg font-bold mb-4 border-b border-slate-100 pb-3">Buat Akun User</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap / Username</Label>
                        <Input class="rounded-lg" id="name" type="text" v-model="form.name" required placeholder="Nama akan digunakan sebagai Username"
                            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-xs text-red-500 font-medium">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="nim_nip">NIM / NIP</Label>
                        <Input class="rounded-lg" id="nim_nip" type="text" v-model="form.nim_nip" required placeholder="Kosongkan jika admin"
                            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.nim_nip }" />
                        <p v-if="form.errors.nim_nip" class="text-xs text-red-500 font-medium">{{ form.errors.nim_nip }}
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="role">Role</Label>
                            <Select v-model="form.role">
                                <SelectTrigger :class="{ 'border-red-500 focus:ring-red-500': form.errors.role }">
                                    <SelectValue placeholder="Pilih" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mahasiswa">Mahasiswa</SelectItem>
                                    <SelectItem value="dosen">Dosen</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.role" class="text-xs text-red-500 font-medium">{{ form.errors.role }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="password">Password</Label>
                            <Input class="rounded-lg" id="password" type="password" v-model="form.password" required placeholder="Minimal 8 karakter"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.password }" />
                            <p v-if="form.errors.password" class="text-xs text-red-500 font-medium">{{
                                form.errors.password }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input class="rounded-lg" id="email" type="email" v-model="form.email" required placeholder="Gunakan email aktif"
                            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-xs text-red-500 font-medium">{{ form.errors.email }}</p>
                    </div>
                    <div class="pt-4">
                        <Button type="submit" class="w-full bg-[#194872] hover:bg-[#194872]/80 text-white"
                            :disabled="form.processing">
                            Tambahkan Akun
                        </Button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white rounded-xl w-full xl:w-2/3 border border-slate-200 shadow-sm flex flex-col xl:h-full overflow-hidden p-6">

                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 border-b border-slate-100 pb-3 shrink-0 gap-4">
                    <h3 class="text-lg font-bold">Daftar Akun User</h3>
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input v-model="search" type="text" placeholder="Cari nama, NIM, atau email..."
                            class="pl-9 bg-slate-50 border-slate-200 focus-visible:ring-slate-300" />
                    </div>
                </div>

                <div class="border border-slate-200 rounded-lg flex-1 overflow-y-auto relative custom-scrollbar">
                    <Table>
                        <TableHeader class="bg-slate-50 sticky top-0 z-10 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                            <TableRow>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">Nama</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">NIM/NIP</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">Role</TableHead>
                                <TableHead class="font-semibold text-slate-700 py-2 px-4">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="users.length === 0">
                                <TableCell colspan="4" class="text-center text-slate-500 h-32">Belum ada data.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell class="py-2 px-4">
                                    <div class="font-medium text-slate-900 capitalize">{{ user.username }}</div>
                                    <div class="text-xs text-slate-500">{{ user.email }}</div>
                                </TableCell>
                                <TableCell class="text-slate-600 py-2 px-4">{{ user.nim_nip }}</TableCell>
                                <TableCell class="py-2 px-4">
                                    <span
                                        :class="['px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase',
                                            user.role === 'admin' ? 'bg-red-100 text-red-700' :
                                                user.role === 'dosen' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700']">
                                        {{ user.role }}
                                    </span>
                                </TableCell>
                                <TableCell class="py-2 px-4">
                                    <div class="flex gap-2">
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-[#194872] border-[#194872] hover:bg-[#194872]/10"
                                            @click="openEditModal(user)">
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button variant="outline" size="icon"
                                            class="h-8 w-8 text-red-600 border-red-200 hover:bg-red-50"
                                            @click="confirmDelete(user.id)">
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
                    <h3 class="text-lg font-bold text-slate-800">Edit Pengguna</h3>
                    <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="updateSubmit" class="p-6 space-y-4">
                    <div class="space-y-2">
                        <Label for="edit_name">Nama Lengkap / Username</Label>
                        <Input class="rounded-lg" id="edit_name" type="text" v-model="editForm.name" required
                            :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.name }" />
                        <p v-if="editForm.errors.name" class="text-xs text-red-500 font-medium">{{ editForm.errors.name
                            }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit_nim_nip">NIM / NIP</Label>
                        <Input class="rounded-lg" id="edit_nim_nip" type="text" v-model="editForm.nim_nip" required
                            :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.nim_nip }" />
                        <p v-if="editForm.errors.nim_nip" class="text-xs text-red-500 font-medium">{{
                            editForm.errors.nim_nip }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit_role">Peran</Label>
                            <Select v-model="editForm.role" disabled="true">
                                <SelectTrigger :class="{ 'border-red-500': editForm.errors.role }">
                                    <SelectValue placeholder="Pilih" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mahasiswa">Mahasiswa</SelectItem>
                                    <SelectItem value="dosen">Dosen</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="editForm.errors.role" class="text-xs text-red-500 font-medium">{{
                                editForm.errors.role }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="edit_password">Kata Sandi Baru</Label>
                            <Input class="rounded-lg" id="edit_password" type="password" v-model="editForm.password"
                                placeholder="Kosongkan jika tidak perlu"
                                :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.password }" />
                            <p v-if="editForm.errors.password" class="text-xs text-red-500 font-medium">{{
                                editForm.errors.password }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit_email">Email</Label>
                        <Input class="rounded-lg" id="edit_email" type="email" v-model="editForm.email" required
                            :class="{ 'border-red-500 focus-visible:ring-red-500': editForm.errors.email }" />
                        <p v-if="editForm.errors.email" class="text-xs text-red-500 font-medium">{{
                            editForm.errors.email }}</p>
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
                    <AlertDialogTitle>Hapus Pengguna?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini permanen dan tidak dapat dibatalkan. Semua data terkait pengguna ini akan dihapus
                        dari
                        server VCivic.
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