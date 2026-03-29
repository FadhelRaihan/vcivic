<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    ArrowLeft, BookOpen, Users, CheckCircle2,
    FileText, Video, Link as LinkIcon, MessageSquare, Clock
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    team: { type: Object, required: true }
});

// --- PENGELOMPOKAN MAHASISWA ---
// Memisahkan mahasiswa berdasarkan status pivot di database
const pendingStudents = computed(() => {
    return props.team.users?.filter(user => user.pivot.status === 'pending') || [];
});

const activeStudents = computed(() => {
    return props.team.users?.filter(user => user.pivot.status === 'approved') || [];
});

// --- LOGIKA ACC MAHASISWA ---
const approveStudent = (userId, userName) => {
    router.post(route('dosen.classes.approve', { team: props.team.id, user_id: userId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Mahasiswa Disetujui', {
                description: `${userName} sekarang memiliki akses penuh ke kelas ini.`,
            });
        }
    });
};

// Fungsi helper untuk ikon tipe materi
const getIconForType = (type) => {
    if (type === 'pdf') return FileText;
    if (type === 'video') return Video;
    return LinkIcon;
};
</script>

<template>

    <Head :title="`Kelas - ${team.name}`" />

    <AdminLayout>

        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dosen.classes.index')"
                    class="p-2 rounded-md hover:bg-slate-100 text-slate-500 transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
                        {{ team.name }}
                    </h2>
                    <p class="text-sm text-slate-500 font-medium mt-0.5">
                        Kode Akses: <span class="font-mono text-[#194872]">{{ team.join_code }}</span>
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-12">

            <Tabs defaultValue="modul" class="w-full">

                <TabsList
                    class="grid w-full grid-cols-3 bg-white border border-slate-200 h-14 p-1 rounded-xl shadow-sm mb-6">
                    <TabsTrigger value="modul"
                        class="rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all">
                        <BookOpen class="w-4 h-4 mr-2" /> Modul & Diskusi
                    </TabsTrigger>
                    <TabsTrigger value="anggota"
                        class="rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all relative">
                        <Users class="w-4 h-4 mr-2" /> Anggota Kelas
                        <span v-if="pendingStudents.length > 0" class="absolute top-2 right-4 flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                    </TabsTrigger>
                    <TabsTrigger value="progres"
                        class="rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all">
                        <CheckCircle2 class="w-4 h-4 mr-2" /> Rekap Progres
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="modul" class="space-y-4">
                    <div v-for="meeting in team.meetings" :key="meeting.id"
                        class="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between hover:shadow-md transition-shadow">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="bg-[#194872] text-white text-xs font-bold px-2.5 py-1 rounded-md">Pertemuan
                                    {{
                                    meeting.meeting_number }}</span>
                                <h3 class="text-lg font-bold text-slate-800">{{ meeting.title }}</h3>
                            </div>
                            <p class="text-sm text-slate-600">{{ meeting.description || 'Tidak ada deskripsi' }}</p>
                        </div>

                        <Link :href="route('dosen.meetings.show', { team: team.id, meeting: meeting.id })">
                            <Button class="bg-[#194872] hover:bg-[#194872]/90 text-white shadow-sm">
                                Masuk Ruang Kelas
                            </Button>
                        </Link>
                    </div>
                </TabsContent>

                <TabsContent value="anggota" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

                    <div class="bg-white border border-red-100 rounded-xl shadow-sm overflow-hidden"
                        v-if="pendingStudents.length > 0">
                        <div class="bg-red-50/50 border-b border-red-100 p-4">
                            <h3 class="font-bold text-red-800 flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                Menunggu Persetujuan Masuk ({{ pendingStudents.length }})
                            </h3>
                            <p class="text-sm text-red-600/80 mt-1">Mahasiswa di bawah ini menggunakan kode akses Anda.
                                Klik
                                setujui agar mereka bisa melihat materi.</p>
                        </div>
                        <Table>
                            <TableHeader class="bg-slate-50/50">
                                <TableRow>
                                    <TableHead>Nama Mahasiswa</TableHead>
                                    <TableHead>NIM</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="student in pendingStudents" :key="student.id">
                                    <TableCell class="font-medium capitalize">{{ student.username }}</TableCell>
                                    <TableCell class="text-slate-600">{{ student.nim_nip }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button size="sm" @click="approveStudent(student.id, student.username)"
                                            class="bg-green-600 hover:bg-green-700 text-white h-8">
                                            <CheckCircle2 class="w-4 h-4 mr-1.5" /> Setujui
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-bold text-slate-800">Mahasiswa Aktif ({{ activeStudents.length }})</h3>
                        </div>
                        <Table>
                            <TableHeader class="bg-slate-50">
                                <TableRow>
                                    <TableHead class="w-12 text-center">No</TableHead>
                                    <TableHead>Nama Lengkap</TableHead>
                                    <TableHead>NIM</TableHead>
                                    <TableHead>Email</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="activeStudents.length === 0">
                                    <TableCell colspan="4" class="text-center text-slate-500 h-24">Belum ada mahasiswa
                                        yang
                                        tergabung.</TableCell>
                                </TableRow>
                                <TableRow v-for="(student, index) in activeStudents" :key="student.id">
                                    <TableCell class="text-center text-slate-500">{{ index + 1 }}</TableCell>
                                    <TableCell class="font-medium text-slate-900 capitalize">{{ student.username }}
                                    </TableCell>
                                    <TableCell class="text-slate-600">{{ student.nim_nip }}</TableCell>
                                    <TableCell class="text-slate-500 text-sm">{{ student.email }}</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </TabsContent>

                <TabsContent value="progres" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden p-8 text-center">
                        <div
                            class="w-16 h-16 bg-blue-50 text-[#194872] rounded-full flex items-center justify-center mx-auto mb-4">
                            <CheckCircle2 class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Rekap Nilai & Progres</h3>
                        <p class="text-slate-500 max-w-lg mx-auto">
                            Fitur ini sedang dalam tahap pengembangan. Nantinya, Anda bisa melihat tabel matriks berisi
                            nilai
                            tugas dan kehadiran mahasiswa untuk setiap pertemuan di sini.
                        </p>
                    </div>
                </TabsContent>

            </Tabs>
        </div>
    </AdminLayout>
</template>