<script setup>
// Halaman area studi interaktif Mahasiswa berisikan penampil iframe materi, grup diskusi kelas, dan tes kuis.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Button } from '@/Components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import {
    ArrowLeft, BookOpen, MessageSquare, Video, FileText,
    Link as LinkIcon, Send, ClipboardList, CheckCircle2, XCircle, PlayCircle, Presentation, MirrorRectangular
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    meeting: { type: Object, required: true },
    grade: { type: Object, default: null }
});

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const urlParams = new URLSearchParams(window.location.search);
const defaultTab = urlParams.get('tab') || 'materi';
const defaultType = urlParams.get('type') || null;

const activeTab = ref(defaultTab);

const initialContent = defaultType
    ? props.meeting.contents.find(c => defaultType === 'bacaan' ? (c.type === 'pdf' || c.type === 'link') : c.type === defaultType)
    : props.meeting.contents[0];

const selectedContent = ref(initialContent || (props.meeting.contents.length > 0 ? props.meeting.contents[0] : null));
// Parser untuk mentransformasi teks URL YouTube standar menjadi URL Embed (iframe) yang diizinkan browser.
const getEmbedUrl = (url) => {
    if (!url) return '';
    // YouTube
    if (url.includes('youtube.com/watch?v=')) return url.replace('watch?v=', 'embed/');
    if (url.includes('youtu.be/')) return url.replace('youtu.be/', 'youtube.com/embed/');
    // Google Drive: drive.google.com/file/d/{ID}/view → drive.google.com/file/d/{ID}/preview
    if (url.includes('drive.google.com/file/d/')) {
        const match = url.match(/\/file\/d\/([^/]+)/);
        if (match) return `https://drive.google.com/file/d/${match[1]}/preview`;
    }
    // Google Drive: drive.google.com/open?id={ID}
    if (url.includes('drive.google.com/open?id=')) {
        const match = url.match(/open\?id=([^&]+)/);
        if (match) return `https://drive.google.com/file/d/${match[1]}/preview`;
    }
    return url;
};

const quizAnswers = ref({});
const quizForm = useForm({
    answers: {}
});
// Menyimpan string pilihan ganda (A/B/C/D) secara lokal ke memori ref. Mencegah klik jika kuis sudah dinilai.
const selectAnswer = (questionId, option) => {
    if (props.grade) return;
    quizAnswers.value[questionId] = option;
};
// Memvalidasi jumlah butir jawaban terisi; lalu mem-POST array payload ke endpoint grader penilaian backend.
const submitQuiz = () => {
    if (Object.keys(quizAnswers.value).length < props.meeting.quiz.questions.length) {
        toast.error('Belum Selesai', { description: 'Harap jawab semua pertanyaan sebelum mengumpulkan kuis.' });
        return;
    }

    quizForm.answers = quizAnswers.value;
    quizForm.post(route('mahasiswa.meetings.quiz.submit', { meeting: props.meeting.id, quiz: props.meeting.quiz.id }), {
        preserveScroll: true,
        onSuccess: () => toast.success('Kuis Selesai!', { description: 'Jawaban Anda berhasil dikirim dan dinilai.' })
    });
};

const chatContainer = ref(null);
const chatForm = useForm({ body: '' });
const localDiscussions = ref([...props.meeting.discussions]);
// Menganimasikan transisi auto-scroll jendela riwayat percakapan langsung ke koordinat pesan chat paling bawah.
const scrollToBottom = async () => {
    await nextTick();
    setTimeout(() => {
        if (chatContainer.value) chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }, 50);
};

watch(activeTab, (newTab) => { if (newTab === 'diskusi') scrollToBottom(); });
watch(() => props.meeting.discussions, (newVal) => {
    const isNew = newVal.length > localDiscussions.value.length;
    localDiscussions.value = [...newVal];
    if (isNew && activeTab.value === 'diskusi') scrollToBottom();
}, { deep: true });

const groupedDiscussions = computed(() => {
    const groups = {};
    localDiscussions.value.forEach(chat => {
        const date = new Date(chat.created_at);
        const dateKey = `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
        if (!groups[dateKey]) groups[dateKey] = [];
        groups[dateKey].push(chat);
    });
    return groups;
});

let pollingInterval = null;// Membuka koneksi perulangan setiap 5 detik dengan server (long-polling) untuk menarik log diskusi grup terbaru.
const startPolling = () => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['meeting'], preserveScroll: true, preserveState: true });
    }, 5000);
};
// Menyuntikkan balon chat mahasiswa ke daftar UI lokal asinkron, sebelum request pengiriman POST API diresolusi.
const submitChat = () => {
    if (!chatForm.body.trim()) return;
    if (pollingInterval) clearInterval(pollingInterval);

    localDiscussions.value.push({
        id: Date.now(), user_id: authUser.value.id, body: chatForm.body,
        created_at: new Date().toISOString(), user: authUser.value, is_sending: true
    });
    scrollToBottom();

    const url = route('discussions.store', props.meeting.id);
    const messageBody = chatForm.body;
    chatForm.body = '';

    router.post(url, { body: messageBody }, { preserveScroll: true, preserveState: true, onFinish: () => startPolling() });
};

let removeBeforeListener = null;
onMounted(() => {
    scrollToBottom();
    startPolling();
    removeBeforeListener = router.on('before', (event) => {
        // Polling reload menuju URL halaman yang sama — izinkan lewat.
        const isSamePage = event.detail.visit.url.pathname === window.location.pathname;
        if (isSamePage) {
            return;
        }
        // Navigasi ke halaman lain (termasuk logout) — hentikan polling.
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
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
                <Link :href="route('mahasiswa.classes.show', meeting.team_id)"
                    class="p-2 rounded-md hover:bg-slate-100 text-slate-500">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800">
                        <span class="text-[#194872]">Pertemuan {{ meeting.meeting_number }}:</span> {{ meeting.title }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto h-[calc(100vh-9rem)] min-h-[600px] flex flex-col">
            <Tabs v-model="activeTab" class="w-full flex-1 flex flex-col min-h-0">
                <TabsList
                    class="grid w-full grid-cols-3 bg-white border border-slate-200 p-1.5 rounded-xl shadow-sm mb-4 shrink-0 h-auto min-h-[72px]">
                    <TabsTrigger value="materi"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white transition-all h-full">
                        <BookOpen class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Materi & Video</span>
                    </TabsTrigger>
                    <TabsTrigger value="diskusi"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white transition-all h-full">
                        <MessageSquare class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Ruang Diskusi</span>
                    </TabsTrigger>
                    <TabsTrigger value="kuis"
                        class="flex flex-col items-center justify-center gap-1.5 py-2.5 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white transition-all h-full">
                        <ClipboardList class="w-5 h-5 shrink-0" />
                        <span class="text-xs font-medium">Kuis / Latihan</span>
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
                                        :class="['p-1.5 rounded text-white shrink-0 mt-0.5', content.type === 'pdf' ? 'bg-red-500' : content.type === 'video' ? 'bg-blue-500' : content.type === 'ppt' ? 'bg-green-500' : 'bg-yellow-500']">
                                        <Video v-if="content.type === 'video'" class="w-3.5 h-3.5" />
                                        <FileText v-else-if="content.type === 'pdf'" class="w-3.5 h-3.5" />
                                        <Presentation v-else-if="content.type === 'ppt'" class="w-3.5 h-3.5" />
                                        <MirrorRectangular v-else-if="content.type === 'infografis'"
                                            class="w-3.5 h-3.5" />
                                        <LinkIcon v-else class="w-3.5 h-3.5" />
                                    </div>
                                    <span class="font-medium text-sm leading-tight">{{ content.title }}</span>
                                </button>
                                <div v-if="meeting.contents.length === 0"
                                    class="text-sm text-slate-500 text-center p-4">Belum
                                    ada materi.</div>
                            </div>
                        </div>

                        <div v-if="selectedContent" class="w-full h-full flex flex-col min-h-0">

                            <iframe
                                v-if="selectedContent.type === 'video' && (selectedContent.file_url.includes('youtube') || selectedContent.file_url.includes('youtu.be') || selectedContent.file_url.includes('drive.google.com'))"
                                :src="getEmbedUrl(selectedContent.file_url)" class="w-full h-full rounded-lg"
                                allowfullscreen allow="autoplay">
                            </iframe>

                            <video v-else-if="selectedContent.type === 'video'" controls controlsList="nodownload"
                                class="w-full h-full rounded-lg bg-black">
                                <source :src="selectedContent.file_url" type="video/mp4">
                                Maaf, browser Anda tidak mendukung pemutar video.
                            </video>

                            <iframe v-else-if="selectedContent.type === 'pdf'" :src="selectedContent.file_url"
                                class="w-full h-full rounded-lg border-0"></iframe>

                            <div v-else-if="selectedContent.type === 'ppt'" class="flex flex-col h-full space-y-2">
                                <iframe
                                    :src="`https://docs.google.com/gview?url=${encodeURIComponent(selectedContent.file_url)}&embedded=true`"
                                    class="w-full flex-1 h-full rounded-lg border-0 bg-slate-100"></iframe>
                            </div>

                            <div v-else-if="selectedContent.type === 'infografis'"
                                class="flex flex-col h-full space-y-2">
                                <img :src="selectedContent.file_url" alt="Infografis"
                                    class="w-full h-full rounded-lg border-0 bg-slate-100">
                            </div>

                            <div v-else
                                class="flex-1 flex flex-col items-center justify-center text-center bg-slate-50 rounded-lg">
                                <LinkIcon class="w-12 h-12 text-slate-300 mb-4" />
                                <h3 class="text-lg font-bold text-slate-800">Materi Tautan / File Eksternal</h3>
                                <a :href="selectedContent.file_url" target="_blank"
                                    class="px-6 py-2 bg-[#194872] text-white rounded-lg hover:bg-blue-800 mt-4">Buka
                                    di Tab Baru</a>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="diskusi" class="h-full w-full outline-none overflow-y-auto">
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
                                class="text-center py-10 bg-white/50 rounded-lg text-slate-500 text-sm">Belum ada
                                obrolan.</div>
                            <template v-else v-for="(chats, date) in groupedDiscussions" :key="date">
                                <div class="flex justify-center my-5"><span
                                        class="bg-white/80 border border-slate-200 shadow-sm text-slate-500 text-[11px] font-bold px-3 py-1.5 rounded-lg tracking-wide">{{
                                            date }}</span></div>
                                <div v-for="chat in chats" :key="chat.id"
                                    :class="['flex w-full mt-2', chat.user_id === authUser.id ? 'justify-end' : 'justify-start']">
                                    <div
                                        :class="['max-w-[85%] md:max-w-[70%] flex flex-col', chat.user_id === authUser.id ? 'items-end' : 'items-start']">
                                        <span v-if="chat.user_id !== authUser.id"
                                            class="text-xs font-semibold text-slate-500 mb-1 ml-1 capitalize">{{
                                                chat.user?.username }} <span v-if="chat.user?.role === 'dosen'"
                                                class="text-[#194872] ml-1">(Dosen)</span></span>
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
                                    class="bg-[#194872] text-white w-11 h-11 rounded-full p-0 flex-shrink-0 flex items-center justify-center"
                                    :disabled="!chatForm.body.trim()">
                                    <Send class="w-4 h-4" />
                                </Button>
                            </form>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="kuis" class="h-full w-full outline-none">
                    <div
                        class="bg-white rounded-xl border border-slate-200 shadow-sm h-full flex flex-col min-h-0 relative overflow-hidden">

                        <div v-if="!meeting.quiz"
                            class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-slate-50">
                            <ClipboardList class="w-16 h-16 text-slate-300 mb-4" />
                            <h3 class="text-xl font-bold text-slate-800">Tidak Ada Kuis</h3>
                            <p class="text-slate-500 mt-2">Dosen belum membuat kuis untuk pertemuan ini.</p>
                        </div>

                        <div v-else-if="grade"
                            class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-gradient-to-b from-white to-slate-50">
                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Hasil Evaluasi Kuis</h2>
                            <p class="text-slate-500 mb-8">{{ meeting.quiz.title }}</p>

                            <div class="w-48 h-48 bg-white p-2 rounded-full shadow-lg border-2 flex items-center justify-center flex-col mb-6"
                                :class="grade.score >= meeting.quiz.passing_grade ? 'border-green-400' : 'border-red-400'">
                                <span class="text-2xl font-black"
                                    :class="grade.score >= meeting.quiz.passing_grade ? 'text-green-600' : 'text-red-500'">
                                    {{ grade.score }}<span class="text-2xl text-slate-300">/100</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2 px-6 py-3 rounded-full font-bold text-lg shadow-sm"
                                :class="grade.score >= meeting.quiz.passing_grade ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                <CheckCircle2 v-if="grade.score >= meeting.quiz.passing_grade" class="w-6 h-6" />
                                <XCircle v-else class="w-6 h-6" />
                                {{ grade.score >= meeting.quiz.passing_grade ? 'Lulus KKM' : 'Perlu Remedial' }}
                            </div>
                            <p class="text-sm text-slate-400 mt-4">KKM: {{ meeting.quiz.passing_grade }}</p>
                        </div>

                        <div v-else class="flex flex-col h-full min-h-0">
                            <div class="bg-[#194872] text-white p-6 shrink-0">
                                <h3 class="font-bold text-xl">{{ meeting.quiz.title }}</h3>
                                <p class="text-blue-200 text-sm mt-1">Jawablah {{ meeting.quiz.questions.length }}
                                    pertanyaan
                                    berikut dengan benar.</p>
                            </div>

                            <div class="flex-1 overflow-y-auto p-6 space-y-8 custom-scrollbar">
                                <div v-for="(question, index) in meeting.quiz.questions" :key="question.id"
                                    class="bg-white border border-slate-100 p-6 rounded-xl shadow-sm">
                                    <h4 class="text-lg font-medium text-slate-800 mb-5 leading-relaxed">
                                        <span class="font-bold text-[#194872] mr-2">{{ index + 1 }}.</span> {{
                                            question.question_text }}
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <button v-for="option in ['A', 'B', 'C', 'D']" :key="option"
                                            @click="selectAnswer(question.id, option)"
                                            :class="['p-4 rounded-xl border-2 text-left transition-all flex items-start gap-3',
                                                quizAnswers[question.id] === option
                                                    ? 'border-[#194872] bg-blue-50 text-[#194872] font-semibold shadow-sm'
                                                    : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50 text-slate-600']">
                                            <div
                                                :class="['w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5',
                                                    quizAnswers[question.id] === option ? 'border-[#194872] bg-[#194872] text-white' : 'border-slate-300']">
                                                <span class="text-xs font-bold">{{ option }}</span>
                                            </div>
                                            <span class="leading-relaxed">{{ question[`option_${option.toLowerCase()}`]
                                            }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white border-t border-slate-200 p-4 shrink-0 flex justify-between items-center shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
                                <span class="text-slate-500 font-medium">Terjawab: <strong class="text-[#194872]">{{
                                    Object.keys(quizAnswers).length }}</strong> / {{ meeting.quiz.questions.length
                                        }}</span>
                                <Button @click="submitQuiz"
                                    class="bg-orange-500 hover:bg-orange-600 text-white px-8 h-12 text-lg shadow-md font-bold"
                                    :disabled="quizForm.processing">
                                    Submit Jawaban
                                </Button>
                            </div>
                        </div>

                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AdminLayout>
</template>