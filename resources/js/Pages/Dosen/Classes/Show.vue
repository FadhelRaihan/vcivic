<script setup>
// Halaman monitor spesifik suatu kelas untuk memverifikasi mahasiswa, melihat materi tersedia, dan rekap skor kuis.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    ArrowLeft, BookOpen, Users, CheckCircle2,
    FileText, Video, Link as LinkIcon, MessageSquare, Clock, Download
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    team: { type: Object, required: true }
});

const pendingStudents = computed(() => {
    return props.team.users?.filter(user => user.membership.status === 'pending') || [];
});

const activeStudents = computed(() => {
    return props.team.users?.filter(user => user.membership.status === 'approved') || [];
});

// Menyetujui request mahasiswa yang berstatus 'pending' agar mendapat visibilitas ke seluruh modul kelas.
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

// Membantu merender ikon antarmuka yang sesuai berdasarkan jenis ekstensi bahasan ajar.
const getIconForType = (type) => {
    if (type === 'pdf') return FileText;
    if (type === 'video') return Video;
    return LinkIcon;
};

// Mengambil nilai angka dari entitas kuis mahasiswa pada suatu pertemuan spesifik, mereturn 'Belum' jika kosong.
const getStudentScore = (userId, meeting) => {
    if (!meeting.quiz) return null;
    const grade = meeting.quiz.student_grades?.find(g => g.user_id === userId);
    return grade ? grade.score : 'Belum';
};

// Menghitung angka performa rata-rata mahasiswa dari akumulasi seluruh nilai kuis pertemuan yang telah dikerjakan.
const getStudentAverage = (userId) => {
    let totalScore = 0;
    let totalQuizzes = 0;

    props.team.meetings?.forEach(meeting => {
        if (meeting.quiz) {
            totalQuizzes++;
            const grade = meeting.quiz.student_grades?.find(g => g.user_id === userId);
            if (grade) totalScore += grade.score;
        }
    });

    if (totalQuizzes === 0) return '-';
    return Math.round(totalScore / totalQuizzes);
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
                    class="grid w-full grid-cols-3 bg-white border border-slate-200 p-1.5 rounded-xl shadow-sm mb-6 h-auto min-h-[72px]">

                    <TabsTrigger value="modul"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all h-full">
                        <BookOpen class="w-5 h-5" />
                        <span class="text-xs font-medium">Modul & Diskusi</span>
                    </TabsTrigger>

                    <TabsTrigger value="anggota"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all relative h-full">
                        <Users class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Anggota Kelas</span>

                        <span v-if="pendingStudents.length > 0" class="absolute top-2 right-4 flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                    </TabsTrigger>

                    <TabsTrigger value="progres"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all h-full">
                        <CheckCircle2 class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Rekap Progres</span>
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
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden flex flex-col">

                        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Matriks Rekapitulasi Nilai</h3>
                                <p class="text-sm text-slate-500">Pantau progres nilai kuis mahasiswa di seluruh pertemuan.</p>
                            </div>
                            
                            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                                <div class="hidden md:flex items-center gap-4 text-xs font-medium text-slate-500">
                                    <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-green-500"></div> Lulus KKM</span>
                                    <span class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-red-500"></div> Di Bawah KKM</span>
                                </div>
                                
                                <a :href="route('dosen.classes.export', team.id)" 
                                    class="inline-flex items-center justify-center rounded-md text-sm font-bold transition-colors h-10 px-5 bg-green-600 text-white hover:bg-green-700 shadow-sm shrink-0">
                                    <Download class="w-4 h-4 mr-2" /> Export Excel
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-100 border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-4 sticky left-0 bg-slate-100 z-20 w-10 text-center">No</th>
                                        <th
                                            class="px-4 py-4 sticky left-[52px] bg-slate-100 z-20 min-w-[200px] shadow-[4px_0_6px_-2px_rgba(0,0,0,0.05)]">
                                            Nama Mahasiswa</th>

                                        <th v-for="meeting in team.meetings" :key="meeting.id"
                                            class="px-4 py-4 text-center whitespace-nowrap border-l border-slate-200">
                                            P{{ meeting.meeting_number }}
                                        </th>

                                        <th
                                            class="px-4 py-4 text-center font-bold bg-blue-100/50 text-[#194872] border-l border-slate-200 sticky right-0 z-20 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.05)]">
                                            Rata-rata
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="activeStudents.length === 0">
                                        <td :colspan="team.meetings.length + 3"
                                            class="px-4 py-12 text-center text-slate-500 italic">
                                            Belum ada mahasiswa yang disetujui di kelas ini.
                                        </td>
                                    </tr>

                                    <tr v-for="(student, index) in activeStudents" :key="student.id"
                                        class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                                        <td
                                            class="px-4 py-3 sticky left-0 bg-white group-hover:bg-slate-50 z-10 text-center">
                                            {{
                                            index + 1 }}</td>
                                        <td
                                            class="px-4 py-3 sticky left-[52px] bg-white group-hover:bg-slate-50 z-10 shadow-[4px_0_6px_-2px_rgba(0,0,0,0.02)]">
                                            <p class="font-bold text-slate-800 capitalize">{{ student.username }}</p>
                                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ student.nim_nip || '-'
                                                }}</p>
                                        </td>

                                        <td v-for="meeting in team.meetings" :key="meeting.id"
                                            class="px-4 py-3 text-center border-l border-slate-100">
                                            <span v-if="getStudentScore(student.id, meeting) === null"
                                                class="text-slate-300">-</span>
                                            <span v-else-if="getStudentScore(student.id, meeting) === 'Belum'"
                                                class="text-xs font-semibold text-orange-500 bg-orange-50 px-2 py-1 rounded-md">Belum</span>
                                            <span v-else
                                                :class="['font-bold text-base', getStudentScore(student.id, meeting) >= meeting.quiz.passing_grade ? 'text-green-600' : 'text-red-500']">
                                                {{ getStudentScore(student.id, meeting) }}
                                            </span>
                                        </td>

                                        <td
                                            class="px-4 py-3 text-center font-bold text-lg text-[#194872] bg-blue-50/30 border-l border-slate-100 sticky right-0 z-10 group-hover:bg-blue-50/60 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.02)]">
                                            {{ getStudentAverage(student.id) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </TabsContent>

            </Tabs>
        </div>
    </AdminLayout>
</template>