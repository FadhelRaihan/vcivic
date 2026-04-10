<script setup>
// Halaman pengelolaan interaktif Dosen untuk mengedit info detail kelas, jadwal pertemuan, dan upload materi.
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Settings, Plus, Trash2, BookOpen, FileText, Video, Link as LinkIcon, UploadCloud, X, Pencil, Presentation, ListChecks } from 'lucide-vue-next';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { toast } from 'vue-sonner';

const props = defineProps({ team: { type: Object, required: true } });

const editClassForm = useForm({ name: props.team.name });
// Mengirim instruksi penyimpanan pembaruan string nama kelas utama ke sisi Controller Back-End.
const submitEditClass = () => {
    editClassForm.put(route('dosen.classes.update', props.team.id), {
        onSuccess: () => toast.success('Nama kelas diperbarui.'),
        onError: (errors) => {
            Object.values(errors).forEach(err => toast.error(err));
        }
    });
};

const isMeetingModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMeetingId = ref(null);
const meetingForm = useForm({ meeting_number: '', title: '', description: '' });

// Menampilkan jendela form pop-up yang difungsikan untuk menambah pertemuan baru yang masih kosong.
const openAddMeeting = () => { isEditMode.value = false; meetingForm.reset(); isMeetingModalOpen.value = true; };
// Menampilkan jendela form pop-up terisi guna mengedit atribut pertemuan yang sudah ada.
const openEditMeeting = (meeting) => { isEditMode.value = true; selectedMeetingId.value = meeting.id; meetingForm.meeting_number = meeting.meeting_number; meetingForm.title = meeting.title; meetingForm.description = meeting.description || ''; isMeetingModalOpen.value = true; };
// Mengelompokkan logika Request untuk baik menyimpan Meeting (POST) baru maupun Update (PUT) pada mode Edit.
const submitMeeting = () => {
    if (isEditMode.value) {
        meetingForm.put(route('dosen.meetings.update', { team: props.team.id, meeting: selectedMeetingId.value }), { onSuccess: () => { isMeetingModalOpen.value = false; toast.success('Pertemuan diperbarui.'); } });
    } else {
        meetingForm.post(route('dosen.meetings.store', props.team.id), {
            onSuccess: () => { isMeetingModalOpen.value = false; toast.success('Pertemuan dibuat.'); },
            onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

const isContentModalOpen = ref(false);
const isEditContentMode = ref(false);
const selectedContentId = ref(null);
const inputMethod = ref('upload');
const contentForm = useForm({ type: 'pdf', title: '', file_url: '', file_upload: null });

watch(inputMethod, (newMethod) => {
    if (newMethod === 'link') {
        contentForm.file_upload = null;
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';
    } else if (newMethod === 'upload') {
        contentForm.file_url = '';
    }
});

// Membuka jendela form upload/link materi ke dalam pertemuan terpilih, mendeteksi jika ini adalah tindakan edit.
const openContentModal = (meetingId, content = null) => {
    selectedMeetingId.value = meetingId;
    if (content) {
        isEditContentMode.value = true;
        selectedContentId.value = content.id;
        contentForm.type = content.type;
        contentForm.title = content.title;
        inputMethod.value = content.file_url?.includes('storage') ? 'upload' : 'link';
        contentForm.file_url = inputMethod.value === 'link' ? content.file_url : '';
        contentForm.file_upload = null;
    } else {
        isEditContentMode.value = false;
        contentForm.reset();
        inputMethod.value = 'upload';
    }
    isContentModalOpen.value = true;
};

// Menyelesaikan unggahan/edit info tautan resource lampiran materi pertemuan ke endpoint.
const submitContent = () => {
    if (isEditContentMode.value) {
        contentForm.post(route('dosen.meetings.contents.update', {
            team: props.team.id,
            meeting: selectedMeetingId.value,
            content: selectedContentId.value
        }), {
            preserveScroll: true,
            onSuccess: () => {
                isContentModalOpen.value = false;
                toast.success('Materi diperbarui.');
            },
        });
    } else {
        contentForm.post(route('dosen.meetings.contents.store', {
            team: props.team.id,
            meeting: selectedMeetingId.value
        }), {
            preserveScroll: true,
            onSuccess: () => {
                isContentModalOpen.value = false;
                toast.success('Materi diunggah.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

const isDeleteDialogOpen = ref(false);
const deleteType = ref('');

// Pemanggilan pembuka modal alert bertipe penghapusan (destructive), mengeset tipe delete sebagai 'meeting'.
const confirmDeleteMeeting = (id) => { deleteType.value = 'meeting'; selectedMeetingId.value = id; isDeleteDialogOpen.value = true; };
// Pemanggilan pembuka modal alert bertipe penghapusan (destructive), mengeset tipe delete sebagai 'content' materi.
const confirmDeleteContent = (meetingId, contentId) => { deleteType.value = 'content'; selectedMeetingId.value = meetingId; selectedContentId.value = contentId; isDeleteDialogOpen.value = true; };

// Pengeksekusian request Delete final ke server, membedakan route target dependensi antara tabel meeting/contents.
const executeDelete = () => {
    if (deleteType.value === 'meeting') {
        router.delete(route('dosen.meetings.destroy', { team: props.team.id, meeting: selectedMeetingId.value }), { onSuccess: () => { isDeleteDialogOpen.value = false; toast.success('Pertemuan dihapus.'); } });
    } else {
        router.delete(route('dosen.meetings.contents.destroy', { team: props.team.id, meeting: selectedMeetingId.value, content: selectedContentId.value }), {
            onSuccess: () => { isDeleteDialogOpen.value = false; toast.success('Materi dihapus.'); }, onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

// Berfungsi me-return icon komponen Vue (Lucide) spesifik sesuai identitas file extension material.
const getIconForType = (type) => {
    if (type === 'pdf') return FileText;
    if (type === 'ppt') return Presentation;
    if (type === 'video') return Video;
    return LinkIcon;
};
</script>

<template>

    <Head :title="`Edit Kelas - ${team.name}`" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('dosen.classes.index')" class="p-2 rounded-md hover:bg-slate-100 text-slate-500">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800">Ruang Edit: {{ team.name }}</h2>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 p-4 px-6 flex items-center gap-2">
                    <Settings class="w-5 h-5 text-slate-500" />
                    <h3 class="font-bold text-slate-800">Identitas Kelas</h3>
                </div>
                <form @submit.prevent="submitEditClass" class="p-6 flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-2/3 space-y-2"><Label>Nama Kelas</Label><Input v-model="editClassForm.name"
                            required /></div>
                    <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white"
                        :disabled="editClassForm.processing">Simpan
                        Nama</Button>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 p-4 px-6 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <BookOpen class="w-5 h-5 text-[#194872]" />
                        <h3 class="font-bold text-slate-800">Susunan Pertemuan</h3>
                    </div>
                    <Button @click="openAddMeeting" class="bg-[#194872] hover:bg-[#194872]/90 text-white">
                        <Plus class="w-4 h-4 mr-2" /> Tambah Pertemuan
                    </Button>
                </div>

                <div class="p-6 space-y-6">
                    <div v-for="meeting in team.meetings" :key="meeting.id"
                        class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-white border-b border-slate-100 p-5 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded">Pert.
                                        {{
                                            meeting.meeting_number }}</span>
                                    <h4 class="font-bold text-slate-800 text-lg">{{ meeting.title }}</h4>
                                </div>
                                <p class="text-sm text-slate-500">{{ meeting.description }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="openEditMeeting(meeting)"
                                    class="text-blue-600 border-blue-200">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" @click="confirmDeleteMeeting(meeting.id)"
                                    class="text-red-500 border-red-200">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-xs font-bold text-slate-500 uppercase">Materi Pertemuan</h5>
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('dosen.meetings.quiz.manage', { team: team.id, meeting: meeting.id })"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors h-9 px-3 border border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100">
                                        <ListChecks class="w-3.5 h-3.5 mr-1" /> Kelola Kuis
                                    </Link>

                                    <Button variant="outline" size="sm" @click="openContentModal(meeting.id)"
                                        class="text-[#194872] border-blue-200 hover:bg-blue-50">
                                        <UploadCloud class="w-3.5 h-3.5 mr-1" /> Tambah Materi
                                    </Button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="content in meeting.contents" :key="content.id"
                                    class="flex items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-sm group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            :class="['p-2 rounded-md text-white shrink-0', content.type === 'pdf' ? 'bg-red-500' : content.type === 'ppt' ? 'bg-orange-500' : content.type === 'video' ? 'bg-blue-500' : 'bg-green-500']">
                                            <component :is="getIconForType(content.type)" class="w-4 h-4" />
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-semibold truncate">{{ content.title }}</p>
                                            <span class="text-xs text-slate-400 uppercase">{{ content.type }}</span>
                                        </div>
                                    </div>
                                    <div
                                        class="flex items-center gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button variant="ghost" size="icon"
                                            @click="openContentModal(meeting.id, content)"
                                            class="h-8 w-8 text-blue-600">
                                            <Pencil class="w-3.5 h-3.5" />
                                        </Button>
                                        <Button variant="ghost" size="icon"
                                            @click="confirmDeleteContent(meeting.id, content.id)"
                                            class="h-8 w-8 text-red-500">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isMeetingModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">{{ isEditMode ? 'Edit' : 'Tambah' }} Pertemuan</h3>
                <form @submit.prevent="submitMeeting" class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-1/3 space-y-2">
                            <Label>Pert. Ke</Label>
                            <Input type="number" min="1" max="16" v-model="meetingForm.meeting_number" required />
                        </div>
                        <div class="w-2/3 space-y-2">
                            <Label>Judul Modul</Label>
                            <Input type="text" v-model="meetingForm.title" required />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Deskripsi</Label>
                        <textarea v-model="meetingForm.description" rows="3"
                            class="flex w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-4 pt-2">
                        <Button type="button" variant="outline" @click="isMeetingModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white"
                            :disabled="meetingForm.processing">Simpan
                            Pertemuan</Button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isContentModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">{{ isEditContentMode ? 'Edit' : 'Tambah' }} Materi</h3>
                <form @submit.prevent="submitContent" class="space-y-4">
                    <div class="space-y-2 relative">
                        <Label>Tipe File</Label>
                        <Select v-model="contentForm.type" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Tipe" />
                            </SelectTrigger>
                            <SelectContent class="z-[200]">
                                <SelectItem value="pdf">Dokumen (PDF)</SelectItem>
                                <SelectItem value="ppt">Presentasi (PPT/PPTX)</SelectItem>
                                <SelectItem value="video">Video</SelectItem>
                                <SelectItem value="link">Tautan Web Biasa</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2"><Label>Judul Materi</Label><Input type="text" v-model="contentForm.title"
                            required />
                    </div>

                    <div class="space-y-2 relative">
                        <Label>Metode Input</Label>
                        <Select v-model="inputMethod">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih" />
                            </SelectTrigger>
                            <SelectContent class="z-[200]">
                                <SelectItem value="upload">Upload File Fisik</SelectItem>
                                <SelectItem value="link">Gunakan Tautan (URL)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="inputMethod === 'upload'" class="space-y-2">
                        <Label>Pilih File</Label>
                        <Input id="fileInput" type="file" class="cursor-pointer" accept=".pdf,.ppt,.pptx,.mp4"
                            @change="contentForm.file_upload = $event.target.files[0]" :required="!isEditContentMode" />
                        <p v-if="isEditContentMode" class="text-xs text-orange-500 mt-1">*Abaikan jika tidak ingin
                            mengganti
                            file lama.</p>
                    </div>

                    <div v-if="inputMethod === 'link'" class="space-y-2">
                        <Label>URL Tautan</Label>
                        <Input type="url" v-model="contentForm.file_url" placeholder="https://..." required />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button type="button" variant="outline" @click="isContentModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white"
                            :disabled="contentForm.processing">Simpan
                            Materi</Button>
                    </div>
                </form>
            </div>
        </div>

        <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <AlertDialogContent class="z-[110]">
                <AlertDialogHeader>
                    <AlertDialogTitle>Konfirmasi Penghapusan</AlertDialogTitle>
                    <AlertDialogDescription>Tindakan ini permanen. Apakah Anda yakin ingin menghapus data ini?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isDeleteDialogOpen = false">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white">Ya, Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>