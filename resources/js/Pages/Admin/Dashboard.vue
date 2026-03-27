<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/Components/ui/select';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/Components/ui/table';

const props = defineProps({
    users: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'mahasiswa',
    nim_nip: '',
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head title="Panel Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Panel Admin VCivic</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 w-full md:w-1/3 h-fit border border-slate-200">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Daftarkan Pengguna</h3>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap</Label>
                            <Input id="name" type="text" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required />
                            <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="nim_nip">NIM / NIP</Label>
                            <Input id="nim_nip" type="text" v-model="form.nim_nip" required />
                            <p v-if="form.errors.nim_nip" class="text-sm text-red-500">{{ form.errors.nim_nip }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Peran (Role)</Label>
                            <Select v-model="form.role">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Peran" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="mahasiswa">Mahasiswa</SelectItem>
                                    <SelectItem value="dosen">Dosen</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.role" class="text-sm text-red-500">{{ form.errors.role }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Kata Sandi Sementara</Label>
                            <Input id="password" type="password" v-model="form.password" required />
                            <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <div class="pt-4">
                            <Button type="submit" class="w-full" :disabled="form.processing">
                                Daftarkan Akun
                            </Button>
                        </div>
                    </form>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 w-full md:w-2/3 border border-slate-200">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Daftar Pengguna Aktif</h3>

                    <div class="border rounded-md">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama</TableHead>
                                    <TableHead>NIM/NIP</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Role</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="users.length === 0">
                                    <TableCell colspan="4" class="text-center text-slate-500 h-24">
                                        Belum ada pengguna terdaftar.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="user in users" :key="user.id">
                                    <TableCell class="font-medium">{{ user.name }}</TableCell>
                                    <TableCell>{{ user.nim_nip }}</TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'px-2 py-1 rounded-md text-xs font-bold',
                                            user.role === 'dosen' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'
                                        ]">
                                            {{ user.role.toUpperCase() }}
                                        </span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>