<script setup>
// Halaman detail satu kelas di sisi Mahasiswa untuk mengamati modul, progres matriks, dan ketersediaan kuis.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeft, BookOpen, Presentation, PlayCircle,
    ClipboardList, CheckCircle2, ChevronRight, MirrorRectangular,
    MirrorRectangularIcon
} from 'lucide-vue-next';

const props = defineProps({
    team: { type: Object, required: true },
    progress: { type: Object, required: true },
    completedQuizzes: { type: Array, required: true }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
// Fungsi cek kondisional apakah suatu meeting spesifik memiliki setidaknya satu material bernilai 'types' array.
const hasContent = (meeting, types) => {
    return meeting.contents?.some(c => types.includes(c.type));
};
// Mengecek status ketersediaan objek instansi relasi kuis yang berikatan dengan modul pertemuan tersebut.
const hasQuiz = (meeting) => {
    return meeting.quiz !== null;
};
// Memverifikasi apakah id kuis tertentu sudah direkam dalam koleksi array prop 'completedQuizzes' milik user.
const isQuizDone = (quizId) => {
    return props.completedQuizzes.includes(quizId);
};
</script>

<template>

    <Head :title="`Kelas: ${team.name}`" />

    <AdminLayout>
        <div class="max-w-3xl mx-auto pb-12">

            <div
                class="bg-[#194872] rounded-b-3xl sm:rounded-3xl p-6 sm:p-8 mb-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-5 rounded-full -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -ml-10 -mb-10"></div>

                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <Link :href="route('mahasiswa.dashboard')"
                        class="p-2 bg-white/10 hover:bg-white/20 rounded-full backdrop-blur-sm transition-colors">
                        <ArrowLeft class="w-5 h-5 text-white" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold leading-tight">Selamat Datang, <span class="capitalize">{{
                                user.username }}</span>!</h2>
                        <p class="text-blue-200 text-sm opacity-90">{{ team.name }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 flex items-center gap-6 shadow-md relative z-10">
                    <div class="relative w-20 h-20 shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="8" fill="transparent"
                                class="text-slate-100" />
                            <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="8" fill="transparent"
                                :stroke-dasharray="213.6"
                                :stroke-dashoffset="213.6 - (213.6 * progress.percentage) / 100"
                                class="text-green-500 transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-slate-800 font-bold text-lg">{{ progress.percentage }}%</span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-1">Progres Belajar</h3>
                        <p class="text-slate-800 font-bold text-lg">Statistik Ringkas:</p>
                        <p class="text-slate-600 font-medium">{{ progress.completed }} / {{ progress.total }} Pertemuan
                            Selesai</p>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-0">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h3 class="font-bold text-slate-800 text-lg uppercase tracking-wide">{{ team.meetings.length }}
                        Pertemuan</h3>
                </div>

                <div class="space-y-4">
                    <div v-if="team.meetings.length === 0"
                        class="text-center p-10 bg-white rounded-2xl border border-slate-200 text-slate-500">
                        Dosen belum menambahkan pertemuan di kelas ini.
                    </div>

                    <div v-for="meeting in team.meetings" :key="meeting.id"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="pr-4">
                                <h4 class="font-bold text-slate-800 text-base leading-snug">
                                    {{ meeting.meeting_number }}. Pertemuan {{ meeting.meeting_number }} | {{
                                    meeting.title }}
                                </h4>
                            </div>
                            <Link :href="route('mahasiswa.meetings.show', meeting.id)"
                                class="text-slate-400 hover:text-[#194872] shrink-0 mt-1">
                                <ChevronRight class="w-5 h-5" />
                            </Link>
                        </div>

                        <div class="grid grid-cols-5 gap-2 border-t border-slate-100 pt-4">

                            <Link v-if="hasContent(meeting, ['pdf', 'link'])"
                                :href="route('mahasiswa.meetings.show', { meeting: meeting.id, tab: 'materi', type: 'bacaan' })"
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                <BookOpen class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Materi</span>
                            </Link>
                            <div v-else
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed">
                                <BookOpen class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Materi</span>
                            </div>

                            <Link v-if="hasContent(meeting, ['ppt'])"
                                :href="route('mahasiswa.meetings.show', { meeting: meeting.id, tab: 'materi', type: 'ppt' })"
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                <Presentation class="w-6 h-6" />
                                <span class="text-[11px] font-bold">PPT</span>
                            </Link>
                            <div v-else
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed">
                                <Presentation class="w-6 h-6" />
                                <span class="text-[11px] font-bold">PPT</span>
                            </div>

                            <Link v-if="hasContent(meeting, ['video'])"
                                :href="route('mahasiswa.meetings.show', { meeting: meeting.id, tab: 'materi', type: 'video' })"
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                                <PlayCircle class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Video</span>
                            </Link>
                            <div v-else
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed">
                                <PlayCircle class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Video</span>
                            </div>

                            <Link v-if="hasContent(meeting, ['infografis'])"
                                :href="route('mahasiswa.meetings.show', { meeting: meeting.id, tab: 'materi', type: 'infografis' })"
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition-colors">
                                <MirrorRectangular class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Infografis</span>
                            </Link>
                            <div v-else
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed">
                                <MirrorRectangular class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Infografis</span>
                            </div>

                            <Link v-if="hasQuiz(meeting)"
                                :href="route('mahasiswa.meetings.show', { meeting: meeting.id, tab: 'kuis' })"
                                :class="['flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl transition-colors', isQuizDone(meeting.quiz.id) ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' : 'bg-orange-50 text-orange-500 hover:bg-orange-100']">
                                <CheckCircle2 v-if="isQuizDone(meeting.quiz.id)" class="w-6 h-6" />
                                <ClipboardList v-else class="w-6 h-6" />
                                <span class="text-[11px] font-bold">{{ isQuizDone(meeting.quiz.id) ? 'Selesai' : 'Soal'
                                    }}</span>
                            </Link>
                            <div v-else
                                class="flex flex-col items-center justify-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-300 cursor-not-allowed">
                                <ClipboardList class="w-6 h-6" />
                                <span class="text-[11px] font-bold">Soal</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>