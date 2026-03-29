<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { ArrowLeft, BookOpen, MessageSquare, Video, FileText, Link as LinkIcon, Trash2, Send, CornerDownRight } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    team: { type: Object, required: true },
    meeting: { type: Object, required: true }
});

const discussionForm = useForm({ body: '' })

const scrollToBottom = () => {
    setTimeout(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    }, 100);
};

// Pilih materi pertama secara default
const selectedContent = ref(props.meeting.contents.length > 0 ? props.meeting.contents[0] : null);

// Fungsi cerdas mengubah URL YouTube biasa menjadi URL Embed (Iframe)
const getEmbedUrl = (url) => {
    if (!url) return '';
    if (url.includes('youtube.com/watch?v=')) return url.replace('watch?v=', 'embed/');
    if (url.includes('youtu.be/')) return url.replace('youtu.be/', 'youtube.com/embed/');
    return url;
};

const submitDiscussion = () => {
    discussionForm.post(route('discussions.store', props.meeting.id), {
        preserveScroll: true,
        onSuccess: () => {
            discussionForm.reset();
            toast.success('Pesan terkirim!');
        }
    });
};

// Form untuk Balasan (Reply)
const activeReplyId = ref(null);
const replyForm = useForm({ body: '' });

const page = usePage();
const authUser = computed(() => page.props.auth.user);

const chatContainer = ref(null);
const replyingTo = ref(null);
const chatForm = useForm({ body: '' });

const localDiscussions = ref([...props.meeting.discussions]);

watch(() => props.meeting.discussions, (newVal) => {
    localDiscussions.value = [...newVal];
}, { deep: true });

const setReply = (chat) => {
    replyingTo.value = chat;
    document.getElementById('chatInput').focus();
};

const submitChat = () => {
    if (!chatForm.body.trim()) return;

    const tempId = Date.now();
    // UBAH INI: Gunakan localDiscussions, bukan props
    localDiscussions.value.push({
        id: tempId,
        user_id: authUser.value.id,
        body: chatForm.body,
        created_at: new Date().toISOString(),
        user: authUser.value,
        parent: replyingTo.value ? { ...replyingTo.value } : null,
        is_sending: true
    });

    scrollToBottom();

    const url = replyingTo.value
        ? route('discussions.replies.store', replyingTo.value.id)
        : route('discussions.store', props.meeting.id);

    const messageBody = chatForm.body;
    chatForm.body = '';
    replyingTo.value = null;

    router.post(url, { body: messageBody }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const deleteChat = (id) => {
    // UBAH INI: Gunakan localDiscussions
    const index = localDiscussions.value.findIndex(d => d.id === id);
    if (index !== -1) localDiscussions.value.splice(index, 1);

    router.delete(route('discussions.destroy', id), {
        preserveScroll: true,
        preserveState: true,
    });
};

const toggleReplyForm = (discussionId) => {
    if (activeReplyId.value === discussionId) {
        activeReplyId.value = null; // Tutup form jika diklik lagi
    } else {
        activeReplyId.value = discussionId;
        replyForm.reset();
    }
};

const submitReply = (discussionId) => {
    replyForm.post(route('discussions.replies.store', discussionId), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            activeReplyId.value = null;
            toast.success('Balasan terkirim!');
        }
    });
};

const deleteDiscussion = (discussionId) => {
    if (confirm('Yakin ingin menghapus diskusi ini?')) {
        router.delete(route('discussions.destroy', discussionId), {
            preserveScroll: true,
            onSuccess: () => toast.success('Diskusi dihapus.')
        });
    }
};

let pollingInterval = null;

onMounted(() => {
    scrollToBottom();
    // Jalankan request ke server setiap 5 detik (5000 milidetik)
    pollingInterval = setInterval(() => {
        router.reload({
            only: ['meeting'], // HANYA perbarui prop 'meeting' (yang berisi diskusi)
            preserveScroll: true, // Jangan geser layar
            preserveState: true,  // Jangan reset form yang sedang diketik
        });
    }, 5000);
});

onUnmounted(() => {
    // WAJIB: Hentikan interval saat Dosen pindah ke halaman lain 
    // agar tidak membebani server
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
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

        <div class="max-w-7xl mx-auto pb-12">

            <Tabs defaultValue="materi" class="w-full">
                <TabsList
                    class="bg-white border border-slate-200 h-14 p-1 rounded-xl shadow-sm mb-6 flex w-fit mx-auto">
                    <TabsTrigger value="materi"
                        class="w-48 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white">
                        <BookOpen class="w-4 h-4 mr-2" /> Layar Materi
                    </TabsTrigger>
                    <TabsTrigger value="diskusi"
                        class="w-48 rounded-lg data-[state=active]:bg-[#194872] data-[state=active]:text-white">
                        <MessageSquare class="w-4 h-4 mr-2" /> Forum Diskusi
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="materi">
                    <div class="flex flex-col lg:flex-row gap-6">

                        <div class="w-full lg:w-1/4 bg-white rounded-xl border border-slate-200 shadow-sm p-4 h-fit">
                            <h3 class="font-bold text-slate-800 mb-4 px-2">Daftar Bahan Ajar</h3>
                            <div class="space-y-2">
                                <button v-for="content in meeting.contents" :key="content.id"
                                    @click="selectedContent = content"
                                    :class="['w-full text-left p-3 rounded-lg flex items-start gap-3 transition-colors',
                                        selectedContent?.id === content.id ? 'bg-blue-50 border-blue-200 border text-[#194872]' : 'hover:bg-slate-50 border border-transparent text-slate-700']">
                                    <div
                                        :class="['p-1.5 rounded text-white shrink-0 mt-0.5', content.type === 'pdf' ? 'bg-red-500' : content.type === 'video' ? 'bg-blue-500' : 'bg-green-500']">
                                        <Video v-if="content.type === 'video'" class="w-3.5 h-3.5" />
                                        <FileText v-else-if="content.type === 'pdf'" class="w-3.5 h-3.5" />
                                        <LinkIcon v-else class="w-3.5 h-3.5" />
                                    </div>
                                    <span class="font-medium text-sm leading-tight">{{ content.title }}</span>
                                </button>

                                <div v-if="meeting.contents.length === 0"
                                    class="text-sm text-slate-500 text-center p-4">
                                    Belum ada materi.
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-3/4 bg-white rounded-xl border border-slate-200 shadow-sm p-2">
                            <div v-if="selectedContent">
                                <iframe
                                    v-if="selectedContent.type === 'video' && selectedContent.file_url.includes('youtu')"
                                    :src="getEmbedUrl(selectedContent.file_url)" class="w-full aspect-video rounded-lg"
                                    allowfullscreen>
                                </iframe>

                                <iframe v-else-if="selectedContent.type === 'pdf'" :src="selectedContent.file_url"
                                    class="w-full h-[600px] rounded-lg border-0">
                                </iframe>

                                <div v-else-if="selectedContent.type === 'ppt'" class="space-y-2">
                                    <div
                                        class="bg-orange-50 p-3 rounded-lg text-sm text-orange-800 border border-orange-200">
                                        <strong>Info:</strong> Jika PPT tidak muncul (blank), pastikan aplikasi VCivic
                                        Anda
                                        sudah di-online-kan (hosting). Google Viewer tidak bisa membaca file dari
                                        'localhost'.
                                    </div>
                                    <iframe
                                        :src="`https://docs.google.com/gview?url=${encodeURIComponent(selectedContent.file_url)}&embedded=true`"
                                        class="w-full h-[600px] rounded-lg border-0 bg-slate-100">
                                    </iframe>
                                    <div class="text-right">
                                        <a :href="selectedContent.file_url" target="_blank"
                                            class="text-sm text-blue-600 hover:underline">Download / Buka File
                                            Alternatif</a>
                                    </div>
                                </div>

                                <div v-else
                                    class="h-[400px] flex flex-col items-center justify-center text-center bg-slate-50 rounded-lg">
                                    <LinkIcon class="w-12 h-12 text-slate-300 mb-4" />
                                    <h3 class="text-lg font-bold text-slate-800">Materi Tautan / File Eksternal</h3>
                                    <p class="text-slate-500 mt-2 mb-4">Materi ini tidak dapat ditampilkan di dalam web.
                                    </p>
                                    <a :href="selectedContent.file_url" target="_blank"
                                        class="px-6 py-2 bg-[#194872] text-white rounded-lg hover:bg-blue-800">
                                        Buka di Tab Baru
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </TabsContent>

                <TabsContent value="diskusi">
                    <div
                        class="bg-slate-200/60 rounded-xl border border-slate-200 shadow-sm flex flex-col h-[700px] relative overflow-hidden">

                        <div class="p-4 bg-white shadow-sm z-10 flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-[#194872] rounded-full flex items-center justify-center text-white">
                                <Users class="w-5 h-5" />
                            </div>
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

                            <div v-for="chat in localDiscussions" :key="chat.id"
                                :class="['flex w-full group', chat.user_id === authUser.id ? 'justify-end' : 'justify-start']">

                                <div
                                    :class="['max-w-[85%] md:max-w-[70%] flex flex-col', chat.user_id === authUser.id ? 'items-end' : 'items-start']">

                                    <span v-if="chat.user_id !== authUser.id"
                                        class="text-xs font-semibold text-slate-500 mb-1 ml-1 capitalize">
                                        {{ chat.user?.username }}
                                        <span v-if="chat.user?.role === 'dosen'"
                                            class="text-[#194872] ml-1">(Dosen)</span>
                                    </span>

                                    <div :class="['relative p-3 shadow-sm',
                                        chat.user_id === authUser.id
                                            ? 'bg-[#194872] text-white rounded-2xl rounded-tr-sm'
                                            : 'bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-sm',
                                        chat.is_sending ? 'opacity-70' : 'opacity-100'
                                    ]">

                                        <div v-if="chat.parent"
                                            :class="['mb-2 p-2 rounded text-sm border-l-4 cursor-pointer',
                                                chat.user_id === authUser.id ? 'bg-white/20 border-white/50 text-white' : 'bg-slate-100 border-[#194872] text-slate-600']">
                                            <span class="font-bold text-xs">{{ chat.parent.user?.username || 'PesanDihapus'}}</span>
                                            <p class="truncate text-xs opacity-90">{{ chat.parent.body || 'Pesan ini telahdihapus.' }}</p>
                                        </div>

                                        <p class="text-[14px] leading-relaxed whitespace-pre-wrap">{{ chat.body }}</p>

                                        <div
                                            :class="['text-[10px] mt-1.5 flex items-center justify-end gap-1', chat.user_id === authUser.id ? 'text-blue-200' : 'text-slate-400']">
                                            <span>{{ new Date(chat.created_at).toLocaleTimeString('id-ID',
                                                {
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                }) }}</span>
                                        </div>
                                    </div>

                                    <div
                                        :class="['flex gap-3 text-xs mt-1 opacity-0 group-hover:opacity-100 transition-opacity', chat.user_id === authUser.id ? 'mr-1' : 'ml-1']">
                                        <button @click="setReply(chat)"
                                            class="text-slate-500 hover:text-blue-600 font-medium">Balas</button>
                                        <button
                                            v-if="chat.user_id === authUser.id || authUser.role === 'admin' || authUser.role === 'dosen'"
                                            @click="deleteChat(chat.id)"
                                            class="text-red-400 hover:text-red-600 font-medium">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-3 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] z-10 flex flex-col gap-2">

                            <div v-if="replyingTo"
                                class="bg-slate-100 border-l-4 border-[#194872] p-2 rounded flex justify-between items-center text-sm">
                                <div class="overflow-hidden">
                                    <span class="text-[#194872] font-bold text-xs block">Membalas {{
                                        replyingTo.user?.username
                                        }}</span>
                                    <span class="text-slate-500 truncate block text-xs">{{ replyingTo.body }}</span>
                                </div>
                                <button @click="replyingTo = null" class="text-slate-400 hover:text-red-500 p-1">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>

                            <form @submit.prevent="submitChat" class="flex items-end gap-2">
                                <textarea id="chatInput" v-model="chatForm.body" rows="1"
                                    class="flex-1 max-h-32 min-h-[44px] rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#194872] resize-none custom-scrollbar"
                                    placeholder="Ketik pesan..." required
                                    @keydown.enter.prevent="submitChat"></textarea>

                                <Button type="submit"
                                    class="bg-[#194872] hover:bg-[#194872]/90 w-11 h-11 rounded-full p-0 flex-shrink-0 flex items-center justify-center"
                                    :disabled="!chatForm.body.trim()">
                                    <Send class="w-5 h-5 ml-1" />
                                </Button>
                            </form>
                        </div>
                    </div>
                </TabsContent>

            </Tabs>
        </div>
    </AdminLayout>
</template>