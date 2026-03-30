<script setup>
// Halaman ruang belajar aktif sebuah modul bagi dosen untuk memutar video materi dan meninjau ruang percakapan.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Button } from '@/Components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, BookOpen, MessageSquare, Video, FileText, Link as LinkIcon, Trash2, Send, CornerDownRight, ClipboardList, CheckCircle, XCircle, Download } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    team: { type: Object, required: true },
    meeting: { type: Object, required: true }
});

const activeTab = ref('materi');

const chatContainer = ref(null);
// Secara programatik menggeser scrollbar area chat ke posisi file terbawah untuk memfokuskan pesan terbaru.
const scrollToBottom = async () => {
    await nextTick();
    setTimeout(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    }, 50);
};

watch(activeTab, (newTab) => {
    if (newTab === 'diskusi') {
        scrollToBottom();
    }
});

const selectedContent = ref(props.meeting.contents.length > 0 ? props.meeting.contents[0] : null);

// Mengkonversi format URL YouTube biasa/share menjadi link 'embed/' agar dapat diputar natively di dalam iFrame.
const getEmbedUrl = (url) => {
    if (!url) return '';
    if (url.includes('youtube.com/watch?v=')) return url.replace('watch?v=', 'embed/');
    if (url.includes('youtu.be/')) return url.replace('youtu.be/', 'youtube.com/embed/');
    return url;
};

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const chatForm = useForm({ body: '' });
const localDiscussions = ref([...props.meeting.discussions]);

watch(() => props.meeting.discussions, (newVal) => {
    const isNewMessage = newVal.length > localDiscussions.value.length;
    localDiscussions.value = [...newVal];

    if (isNewMessage && activeTab.value === 'diskusi') {
        scrollToBottom();
    }
}, { deep: true });

const groupedDiscussions = computed(() => {
    const groups = {};
    localDiscussions.value.forEach(chat => {
        const date = new Date(chat.created_at);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        const dateKey = `${day}/${month}/${year}`;

        if (!groups[dateKey]) {
            groups[dateKey] = [];
        }
        groups[dateKey].push(chat);
    });
    return groups;
});

// Menyisipkan pesan ke lokal buffer agar terasa instan (optimistic UI), sebelum dikirim secara asinkron ke server.
const submitChat = () => {
    if (!chatForm.body.trim()) return;

    if (pollingInterval) clearInterval(pollingInterval);

    const tempId = Date.now();
    localDiscussions.value.push({
        id: tempId,
        user_id: authUser.value.id,
        body: chatForm.body,
        created_at: new Date().toISOString(),
        user: authUser.value,
        is_sending: true
    });

    scrollToBottom();

    const url = route('discussions.store', props.meeting.id);
    const messageBody = chatForm.body;
    chatForm.body = '';

    router.post(url, { body: messageBody }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => startPolling()
    });
};

let pollingInterval = null;
let removeBeforeListener = null;

// Membuka koneksi perulangan setiap 5 detik dengan server (long-polling) untuk menarik pembaruan pesan obrolan kelas.
const startPolling = () => {
    pollingInterval = setInterval(() => {
        router.reload({
            only: ['meeting'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 5000);
};

onMounted(() => {
    scrollToBottom();
    startPolling();

    removeBeforeListener = router.on('before', () => {
        if (pollingInterval) clearInterval(pollingInterval);

        selectedContent.value = null;
        localDiscussions.value = [];
    });
});

onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
    if (removeBeforeListener) removeBeforeListener();
});
</script>

<template>

    <Head :title="`Pertemuan ${meeting.meeting_number} - ${meeting.title}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dosen.classes.show', team.id)"
                    class="p-2 rounded-md hover:bg-slate-100 text-slate-500">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800">
                        <span class="text-[#194872]">Pertemuan {{ meeting.meeting_number }}:</span> {{ meeting.title }}
                    </h2>
                    <p class="text-sm text-slate-500">{{ team.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto h-[calc(100vh-9rem)] min-h-[600px] flex flex-col">

            <Tabs v-model="activeTab" class="w-full flex-1 flex flex-col min-h-0">
                <TabsList
                    class="grid w-full grid-cols-3 bg-white border border-slate-200 p-1.5 rounded-xl shadow-sm mb-4 shrink-0 h-auto min-h-[72px]">

                    <TabsTrigger value="materi"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all h-full">
                        <BookOpen class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Layar Materi</span>
                    </TabsTrigger>

                    <TabsTrigger value="diskusi"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all relative h-full">
                        <MessageSquare class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Forum Diskusi</span>
                    </TabsTrigger>

                    <TabsTrigger value="rekap"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white data-[state=active]:shadow-md transition-all h-full">
                        <ClipboardList class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Rekap Nilai</span>
                    </TabsTrigger>

                </TabsList>

                <TabsContent value="materi" class="h-full w-full outline-none">
                    <div class="flex flex-col lg:flex-row gap-6 h-full min-h-0">
                        <div
                            class="w-full lg:w-1/4 bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col h-full min-h-0">
                            <h3 class="font-bold text-slate-800 mb-4 px-2 shrink-0">Daftar Bahan Ajar</h3>
                            <div class="space-y-2 flex-1 overflow-y-auto custom-scrollbar pr-1">
                                <button v-for="content in meeting.contents" :key="content.id"
                                    @click="selectedContent = content"
                                    :class="['w-full text-left p-3 rounded-lg flex items-start gap-3 transition-colors', selectedContent?.id === content.id ? 'bg-blue-50 border-blue-200 border text-[#194872]' : 'hover:bg-slate-50 border border-transparent text-slate-700']">
                                    <div
                                        :class="['p-1.5 rounded text-white shrink-0 mt-0.5', content.type === 'pdf' ? 'bg-red-500' : content.type === 'video' ? 'bg-blue-500' : 'bg-green-500']">
                                        <Video v-if="content.type === 'video'" class="w-3.5 h-3.5" />
                                        <FileText v-else-if="content.type === 'pdf'" class="w-3.5 h-3.5" />
                                        <LinkIcon v-else class="w-3.5 h-3.5" />
                                    </div>
                                    <span class="font-medium text-sm leading-tight">{{ content.title }}</span>
                                </button>
                                <div v-if="meeting.contents.length === 0"
                                    class="text-sm text-slate-500 text-center p-4">Belum
                                    ada materi.</div>
                            </div>
                        </div>

                        <div
                            class="w-full lg:w-3/4 bg-white rounded-xl border border-slate-200 shadow-sm p-2 flex flex-col h-full min-h-0">
                            <div v-if="selectedContent" class="w-full h-full flex flex-col min-h-0">
                                <iframe
                                    v-if="selectedContent.type === 'video' && selectedContent.file_url.includes('youtu')"
                                    :src="getEmbedUrl(selectedContent.file_url)" class="w-full h-full rounded-lg"
                                    allowfullscreen></iframe>
                                <iframe v-else-if="selectedContent.type === 'pdf'" :src="selectedContent.file_url"
                                    class="w-full h-full rounded-lg border-0"></iframe>
                                <div v-else-if="selectedContent.type === 'ppt'" class="flex flex-col h-full space-y-2">
                                    <div
                                        class="bg-orange-50 p-3 rounded-lg text-sm text-orange-800 border border-orange-200 shrink-0">
                                        <strong>Info:</strong> Jika PPT blank, pastikan VCivic sudah di-online-kan.
                                    </div>
                                    <iframe
                                        :src="`https://docs.google.com/gview?url=${encodeURIComponent(selectedContent.file_url)}&embedded=true`"
                                        class="w-full flex-1 h-full rounded-lg border-0 bg-slate-100"></iframe>
                                    <div class="text-right shrink-0"><a :href="selectedContent.file_url" target="_blank"
                                            class="text-sm text-blue-600 hover:underline">Download</a></div>
                                </div>
                                <div v-else
                                    class="flex-1 flex flex-col items-center justify-center text-center bg-slate-50 rounded-lg">
                                    <LinkIcon class="w-12 h-12 text-slate-300 mb-4" />
                                    <h3 class="text-lg font-bold text-slate-800">Materi Tautan / File Eksternal</h3>
                                    <a :href="selectedContent.file_url" target="_blank"
                                        class="px-6 py-2 bg-[#194872] text-white rounded-lg hover:bg-blue-800 mt-4">Buka
                                        di Tab
                                        Baru</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="diskusi" class="h-full w-full outline-none overflow-y-auto relative">
                    <div
                        class="bg-slate-200/60 rounded-xl border border-slate-200 shadow-sm flex flex-col h-full relative overflow-hidden">

                        <div class="py-4 px-6 bg-white shadow-sm z-10 flex items-center gap-3 shrink-0">
                            <div>
                                <h3 class="font-bold text-slate-800 leading-tight">Grup Diskusi Kelas</h3>
                                <p class="text-xs text-slate-500">Pertemuan {{ meeting.meeting_number }}</p>
                            </div>
                        </div>

                        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                            <div v-if="!localDiscussions || localDiscussions.length === 0"
                                class="text-center py-10 bg-white/50 rounded-lg text-slate-500 text-sm">
                                Belum ada obrolan. Sapa kelas sekarang!
                            </div>

                            <template v-else v-for="(chats, date) in groupedDiscussions" :key="date">

                                <div class="flex justify-center my-5">
                                    <span
                                        class="bg-white/80 border border-slate-200 shadow-sm text-slate-500 text-[11px] font-bold px-3 py-1.5 rounded-lg tracking-wide">
                                        {{ date }}
                                    </span>
                                </div>

                                <div v-for="chat in chats" :key="chat.id"
                                    :class="['flex w-full mt-2', chat.user_id === authUser.id ? 'justify-end' : 'justify-start']">
                                    <div
                                        :class="['max-w-[85%] md:max-w-[70%] flex flex-col', chat.user_id === authUser.id ? 'items-end' : 'items-start']">

                                        <span v-if="chat.user_id !== authUser.id"
                                            class="text-xs font-semibold text-slate-500 mb-1 ml-1 capitalize">
                                            {{ chat.user?.username }} <span v-if="chat.user?.role === 'dosen'"
                                                class="text-[#194872] ml-1">(Dosen)</span>
                                        </span>

                                        <div
                                            :class="['relative p-3 shadow-sm', chat.user_id === authUser.id ? 'bg-[#194872] text-white rounded-2xl rounded-tr-sm' : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-sm', chat.is_sending ? 'opacity-70' : 'opacity-100']">
                                            <p class="text-[14px] leading-relaxed whitespace-pre-wrap">{{ chat.body }}
                                            </p>
                                            <div
                                                :class="['text-[10px] mt-1.5 flex items-center justify-end gap-1', chat.user_id === authUser.id ? 'text-blue-200' : 'text-slate-400']">
                                                <span>{{ new Date(chat.created_at).toLocaleTimeString('id-ID', {
                                                    hour:
                                                        '2-digit', minute: '2-digit'
                                                }) }}</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </template>
                        </div>

                        <div
                            class="bg-white p-3 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] z-10 flex flex-col gap-2 shrink-0">
                            <form @submit.prevent="submitChat" class="flex items-end gap-2">
                                <textarea id="chatInput" v-model="chatForm.body" rows="1"
                                    class="flex-1 max-h-32 min-h-[44px] rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#194872] resize-none custom-scrollbar"
                                    placeholder="Ketik pesan diskusi..." required
                                    @keydown.enter.prevent="submitChat"></textarea>

                                <Button type="submit"
                                    class="bg-[#194872] hover:bg-[#194872]/90 w-11 h-11 rounded-full flex items-center justify-center"
                                    :disabled="!chatForm.body.trim()">
                                    <Send class="w-4 h-4" />
                                </Button>
                            </form>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="rekap" class="h-full w-full outline-none">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 h-full flex flex-col min-h-0">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 shrink-0">
                            <div
                                class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                                <ClipboardList class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Rekapitulasi Nilai Kuis</h3>
                                <p class="text-sm text-slate-500">Hasil pengerjaan kuis mahasiswa.</p>
                            </div>
                        </div>

                        <div v-if="!meeting.quiz"
                            class="flex-1 flex items-center justify-center text-center p-12 text-slate-500 bg-slate-50 rounded-lg border border-slate-100">
                            Kuis belum dibuat untuk pertemuan ini. Silakan buat kuis terlebih dahulu melalui menu
                            <b>Kelola
                                Kuis</b>.
                        </div>

                        <div v-else class="flex flex-col flex-1 min-h-0">
                            <div class="mb-4 flex gap-4 shrink-0">
                                <div
                                    class="bg-blue-50 text-blue-800 px-4 py-2 rounded-lg text-sm border border-blue-100">
                                    <span class="font-semibold">KKM (Nilai Lulus):</span> {{ meeting.quiz.passing_grade
                                    }}
                                </div>
                                <div
                                    class="bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm border border-slate-200">
                                    <span class="font-semibold">Total Mengerjakan:</span> {{
                                        meeting.quiz.student_grades?.length
                                        || 0 }} Mahasiswa
                                </div>
                                <a :href="route('dosen.meetings.quiz.export', { team: team.id, meeting: meeting.id })"
                                    class="inline-flex items-center justify-center rounded-md text-sm font-bold transition-colors h-10 px-5 bg-green-600 text-white hover:bg-green-700 shadow-sm shrink-0">
                                    <Download class="w-4 h-4 mr-2" /> Export Excel
                                </a>
                            </div>
                            <div
                                class="overflow-x-auto overflow-y-auto flex-1 rounded-lg border border-slate-200 custom-scrollbar">
                                <table class="w-full text-sm text-left text-slate-500 relative">
                                    <thead
                                        class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-10">
                                        <tr>
                                            <th scope="col" class="px-6 py-4">No</th>
                                            <th scope="col" class="px-6 py-4">NIM/NIP</th>
                                            <th scope="col" class="px-6 py-4">Nama Mahasiswa</th>
                                            <th scope="col" class="px-6 py-4 text-center">Waktu Submit</th>
                                            <th scope="col" class="px-6 py-4 text-center">Nilai</th>
                                            <th scope="col" class="px-6 py-4 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-if="!meeting.quiz.student_grades || meeting.quiz.student_grades.length === 0">
                                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">Belum
                                                ada
                                                mahasiswa yang mengerjakan kuis ini.</td>
                                        </tr>
                                        <tr v-for="(grade, index) in meeting.quiz.student_grades" :key="grade.id"
                                            class="bg-white border-b border-slate-100 hover:bg-slate-50">
                                            <td class="px-6 py-4">{{ index + 1 }}</td>
                                            <td class="px-6 py-4 font-mono">{{ grade.user?.nim_nip || '-' }}</td>
                                            <td class="px-6 py-4 font-medium text-slate-800 capitalize">{{
                                                grade.user?.username
                                                }}</td>
                                            <td class="px-6 py-4 text-center text-xs text-slate-500">{{ new
                                                Date(grade.created_at).toLocaleString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    hour: '2-digit', minute: '2-digit'
                                                }) }}</td>
                                            <td class="px-6 py-4 text-center font-bold text-lg"
                                                :class="grade.score >= meeting.quiz.passing_grade ? 'text-green-600' : 'text-red-500'">
                                                {{ grade.score }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span v-if="grade.score >= meeting.quiz.passing_grade"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <CheckCircle class="w-3 h-3" /> Lulus
                                                </span>
                                                <span v-else
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <XCircle class="w-3 h-3" /> Remedial
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AdminLayout>
</template>