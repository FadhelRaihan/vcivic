<script setup>
// Tampilan kontrol khusus fitur penciptaan soal dan setelan kriteria KKM untuk kuis opsional pada suatu pertemuan.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Settings, Plus, Trash2, ListChecks, CheckCircle } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps({
    team: { type: Object, required: true },
    meeting: { type: Object, required: true },
    quiz: { type: Object, default: null }
});

const quizForm = useForm({
    title: props.quiz?.title || `Kuis Evaluasi: ${props.meeting.title}`,
    passing_grade: props.quiz?.passing_grade || 70,
});

// Menyimpan konfigurasi dasar identitas seperti judul tes dan angka standar minimal kelulusan (KKM) kuis.
const submitQuizSettings = () => {
    quizForm.post(route('dosen.meetings.quiz.store', { team: props.team.id, meeting: props.meeting.id }), {
        preserveScroll: true,
        onSuccess: () => toast.success('Pengaturan kuis berhasil disimpan.')
    });
};

const questionForm = useForm({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct_answer: 'A',
});

// Push atau menambah entitas butir soal pertanyaan komplit dengan 4 pihan ganti dan string kunci jawabannya.
const submitQuestion = () => {
    questionForm.post(route('dosen.meetings.quiz.questions.store', { team: props.team.id, meeting: props.meeting.id, quiz: props.quiz.id }), {
        preserveScroll: true,
        onSuccess: () => {
            questionForm.reset();
            toast.success('Soal berhasil ditambahkan ke dalam kuis.');
        }
    });
};

// Menghancurkan (DELETE) objek butir pertanyaan kuis tertentu menggunakan identifikasi ID-Soal.
const deleteQuestion = (questionId) => {
    if (confirm('Yakin ingin menghapus soal ini?')) {
        router.delete(route('dosen.meetings.quiz.questions.destroy', { team: props.team.id, meeting: props.meeting.id, quiz: props.quiz.id, question: questionId }), {
            preserveScroll: true,
            onSuccess: () => toast.success('Soal dihapus.')
        });
    }
};
</script>

<template>
    <Head :title="`Kelola Kuis - Pertemuan ${meeting.meeting_number}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dosen.classes.manage', team.id)" class="p-2 rounded-md hover:bg-slate-100 text-slate-500">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800">Kelola Kuis: Pert. {{ meeting.meeting_number }}</h2>
                    <p class="text-sm text-slate-500">{{ team.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 p-4 px-6 flex items-center gap-2">
                    <Settings class="w-5 h-5 text-slate-500" />
                    <h3 class="font-bold text-slate-800">Pengaturan Dasar Kuis</h3>
                </div>
                <form @submit.prevent="submitQuizSettings" class="p-6">
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <div class="w-full md:w-2/3 space-y-2">
                            <Label>Judul Kuis</Label>
                            <Input v-model="quizForm.title" required placeholder="Contoh: Kuis Evaluasi Bab 1" />
                        </div>
                        <div class="w-full md:w-1/3 space-y-2">
                            <Label>Nilai Kelulusan (KKM)</Label>
                            <Input type="number" min="0" max="100" v-model="quizForm.passing_grade" required />
                        </div>
                        <div class="pt-6">
                            <Button type="submit" class="bg-slate-800 text-white" :disabled="quizForm.processing">
                                Simpan Pengaturan
                            </Button>
                        </div>
                    </div>
                </form>
            </div>

            <div v-if="!quiz" class="bg-blue-50 border border-blue-200 text-[#194872] p-6 rounded-xl text-center">
                <ListChecks class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <h3 class="font-bold text-lg">Kuis Belum Aktif</h3>
                <p class="text-sm opacity-80 mt-1">Silakan klik "Simpan Pengaturan" di atas terlebih dahulu agar Anda dapat mulai menambahkan soal-soal kuis.</p>
            </div>

            <div v-if="quiz" class="flex flex-col lg:flex-row gap-6">
                
                <div class="w-full lg:w-2/3 space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <ListChecks class="w-5 h-5 text-[#194872]" /> Daftar Soal ({{ quiz.questions?.length || 0 }})
                    </h3>

                    <div v-if="!quiz.questions || quiz.questions.length === 0" class="bg-white border border-slate-200 rounded-xl p-8 text-center text-slate-500">
                        Belum ada soal. Silakan tambahkan soal melalui form di sebelah kanan.
                    </div>

                    <div v-for="(q, index) in quiz.questions" :key="q.id" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm relative group">
                        <div class="flex justify-between items-start gap-4 mb-4">
                            <h4 class="font-bold text-slate-800 text-sm leading-relaxed">
                                <span class="text-[#194872] mr-1">{{ index + 1 }}.</span> {{ q.question_text }}
                            </h4>
                            <button @click="deleteQuestion(q.id)" class="text-red-400 hover:text-red-600 transition-colors shrink-0">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <div :class="['p-2 rounded border', q.correct_answer === 'A' ? 'bg-green-50 border-green-200 text-green-800 font-semibold' : 'bg-slate-50 border-slate-100 text-slate-600']">
                                A. {{ q.option_a }}
                            </div>
                            <div :class="['p-2 rounded border', q.correct_answer === 'B' ? 'bg-green-50 border-green-200 text-green-800 font-semibold' : 'bg-slate-50 border-slate-100 text-slate-600']">
                                B. {{ q.option_b }}
                            </div>
                            <div :class="['p-2 rounded border', q.correct_answer === 'C' ? 'bg-green-50 border-green-200 text-green-800 font-semibold' : 'bg-slate-50 border-slate-100 text-slate-600']">
                                C. {{ q.option_c }}
                            </div>
                            <div :class="['p-2 rounded border', q.correct_answer === 'D' ? 'bg-green-50 border-green-200 text-green-800 font-semibold' : 'bg-slate-50 border-slate-100 text-slate-600']">
                                D. {{ q.option_d }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/3">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm sticky top-6">
                        <div class="bg-[#194872] text-white p-4 rounded-t-xl flex items-center gap-2">
                            <Plus class="w-5 h-5" />
                            <h3 class="font-bold">Tambah Soal Baru</h3>
                        </div>
                        <form @submit.prevent="submitQuestion" class="p-5 space-y-4">
                            <div class="space-y-2">
                                <Label>Pertanyaan</Label>
                                <textarea v-model="questionForm.question_text" rows="3" required class="flex w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#194872]"></textarea>
                            </div>
                            
                            <div class="space-y-2"><Label class="text-xs">Opsi A</Label><Input v-model="questionForm.option_a" required class="h-8 text-sm" /></div>
                            <div class="space-y-2"><Label class="text-xs">Opsi B</Label><Input v-model="questionForm.option_b" required class="h-8 text-sm" /></div>
                            <div class="space-y-2"><Label class="text-xs">Opsi C</Label><Input v-model="questionForm.option_c" required class="h-8 text-sm" /></div>
                            <div class="space-y-2"><Label class="text-xs">Opsi D</Label><Input v-model="questionForm.option_d" required class="h-8 text-sm" /></div>

                            <div class="space-y-2 pt-2 border-t border-slate-100">
                                <Label class="text-green-700 font-bold">Kunci Jawaban Benar</Label>
                                <Select v-model="questionForm.correct_answer" required>
                                    <SelectTrigger class="border-green-200 focus:ring-green-500">
                                        <SelectValue placeholder="Pilih Jawaban" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="A">Opsi A</SelectItem>
                                        <SelectItem value="B">Opsi B</SelectItem>
                                        <SelectItem value="C">Opsi C</SelectItem>
                                        <SelectItem value="D">Opsi D</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <Button type="submit" class="w-full bg-[#194872] hover:bg-[#194872]/90 mt-2" :disabled="questionForm.processing">
                                Simpan Soal
                            </Button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>