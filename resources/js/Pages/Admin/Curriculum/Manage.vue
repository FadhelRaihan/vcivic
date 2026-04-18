<script setup>
// Halaman pengelolaan interaktif Admin untuk mengedit Master Kurikulum (template).
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ArrowLeft, Plus, Trash2, BookOpen, FileText, Video, Link as LinkIcon, UploadCloud, X, Pencil, Presentation, ListChecks, MirrorRectangular, Database } from 'lucide-vue-next';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { toast } from 'vue-sonner';

const props = defineProps({ template: { type: Object, required: true } });

const isMeetingModalOpen = ref(false);
const isEditMode = ref(false);
const selectedMeetingId = ref(null);
const meetingForm = useForm({ meeting_number: '', title: '', description: '' });

const openAddMeeting = () => { isEditMode.value = false; meetingForm.reset(); isMeetingModalOpen.value = true; };
const openEditMeeting = (meeting) => { isEditMode.value = true; selectedMeetingId.value = meeting.id; meetingForm.meeting_number = meeting.meeting_number; meetingForm.title = meeting.title; meetingForm.description = meeting.description || ''; isMeetingModalOpen.value = true; };

const submitMeeting = () => {
    if (isEditMode.value) {
        meetingForm.put(route('admin.curriculum.meetings.update', selectedMeetingId.value), { onSuccess: () => { isMeetingModalOpen.value = false; toast.success('Pertemuan Master diperbarui.'); } });
    } else {
        meetingForm.post(route('admin.curriculum.meetings.store'), {
            onSuccess: () => { isMeetingModalOpen.value = false; toast.success('Pertemuan Master dibuat.'); },
            onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

const isContentModalOpen = ref(false);
const isEditContentMode = ref(false);
const selectedContentId = ref(null);
const contentForm = useForm({ type: 'text', title: '', content: '' });

const openContentModal = (meetingId, content = null) => {
    selectedMeetingId.value = meetingId;
    if (content) {
        isEditContentMode.value = true;
        selectedContentId.value = content.id;
        contentForm.type = content.type;
        contentForm.title = content.title;
        contentForm.content = content.content;
    } else {
        isEditContentMode.value = false;
        contentForm.reset();
    }
    isContentModalOpen.value = true;
};

const submitContent = () => {
    if (isEditContentMode.value) {
        contentForm.post(route('admin.curriculum.meetings.contents.update', selectedContentId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isContentModalOpen.value = false;
                toast.success('Materi Master diperbarui.');
            },
        });
    } else {
        contentForm.post(route('admin.curriculum.meetings.contents.store', selectedMeetingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isContentModalOpen.value = false;
                toast.success('Materi Master ditambahkan.');
            },
            onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

const isDeleteDialogOpen = ref(false);
const deleteType = ref('');

const confirmDeleteMeeting = (id) => { deleteType.value = 'meeting'; selectedMeetingId.value = id; isDeleteDialogOpen.value = true; };
const confirmDeleteContent = (meetingId, contentId) => { deleteType.value = 'content'; selectedMeetingId.value = meetingId; selectedContentId.value = contentId; isDeleteDialogOpen.value = true; };

const executeDelete = () => {
    if (deleteType.value === 'meeting') {
        router.delete(route('admin.curriculum.meetings.destroy', selectedMeetingId.value), { onSuccess: () => { isDeleteDialogOpen.value = false; toast.success('Pertemuan Master dihapus.'); } });
    } else {
        router.delete(route('admin.curriculum.meetings.contents.destroy', selectedContentId.value), {
            onSuccess: () => { isDeleteDialogOpen.value = false; toast.success('Materi Master dihapus.'); },
            onError: (errors) => {
                Object.values(errors).forEach(err => toast.error(err));
            }
        });
    }
};

const getIconForType = (type) => {
    if (type === 'pdf') return FileText;
    if (type === 'ppt') return Presentation;
    if (type === 'video') return Video;
    if (type === 'infografis') return MirrorRectangular;
    return LinkIcon;
};
</script>

<template>
    <Head title="Kelola Master Kurikulum" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.dashboard')" class="p-2 rounded-md hover:bg-slate-100 text-slate-500">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-slate-800">Manajemen Master Kurikulum</h2>
                    <p class="text-sm text-slate-500">Template materi utama untuk semua kelas baru.</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6 pb-12">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-[#194872] p-4 px-6 flex items-center justify-between text-white">
                    <div class="flex items-center gap-2">
                        <Database class="w-5 h-5" />
                        <h3 class="font-bold">Struktur Kurikulum Utama</h3>
                    </div>
                    <Button @click="openAddMeeting" class="bg-white text-[#194872] hover:bg-white/90">
                        <Plus class="w-4 h-4 mr-2" /> Tambah Pertemuan Template
                    </Button>
                </div>

                <div class="p-6 space-y-6">
                    <div v-if="template.meetings.length === 0" class="text-center py-12">
                        <Presentation class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                        <p class="text-slate-500">Belum ada pertemuan dalam template ini.</p>
                    </div>

                    <div v-for="meeting in template.meetings" :key="meeting.id"
                        class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="bg-slate-50 border-b border-slate-200 p-5 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-[#194872] text-white text-xs font-bold px-2 py-0.5 rounded">MODUL {{ meeting.meeting_number }}</span>
                                    <h4 class="font-bold text-slate-800 text-lg">{{ meeting.title }}</h4>
                                </div>
                                <p class="text-sm text-slate-500">{{ meeting.description }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" size="icon" @click="openEditMeeting(meeting)"
                                    class="text-blue-600 border-blue-200 bg-white">
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button variant="outline" size="icon" @click="confirmDeleteMeeting(meeting.id)"
                                    class="text-red-500 border-red-200 bg-white">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>

                        <div class="bg-white p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Materi Template</h5>
                                <Button variant="outline" size="sm" @click="openContentModal(meeting.id)"
                                    class="text-[#194872] border-blue-100 hover:bg-blue-50">
                                    <Plus class="w-3.5 h-3.5 mr-1" /> Tambah Materi
                                </Button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="content in meeting.contents" :key="content.id"
                                    class="flex items-center justify-between gap-3 bg-slate-50 p-3 rounded-lg border border-slate-100 group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            :class="['p-2 rounded-md text-white shrink-0', content.type === 'pdf' ? 'bg-red-500' : content.type === 'video' ? 'bg-blue-500' : content.type === 'ppt' ? 'bg-orange-500' : content.type === 'infografis' ? 'bg-yellow-500' : 'bg-green-500']">
                                            <component :is="getIconForType(content.type)" class="w-4 h-4" />
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-semibold truncate text-slate-700">{{ content.title }}</p>
                                            <span class="text-[10px] text-slate-400 uppercase font-bold">{{ content.type }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button variant="ghost" size="icon" @click="openContentModal(meeting.id, content)"
                                            class="h-8 w-8 text-blue-600">
                                            <Pencil class="w-3.5 h-3.5" />
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="confirmDeleteContent(meeting.id, content.id)"
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

        <!-- Meeting Modal -->
        <div v-if="isMeetingModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-in zoom-in-95 duration-200">
                <h3 class="text-xl font-bold mb-4 text-slate-800">{{ isEditMode ? 'Edit' : 'Tambah' }} Pertemuan Template</h3>
                <form @submit.prevent="submitMeeting" class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-1/3 space-y-2">
                            <Label>Modul Ke-</Label>
                            <Input type="number" min="1" max="16" v-model="meetingForm.meeting_number" required />
                        </div>
                        <div class="w-2/3 space-y-2">
                            <Label>Judul Modul</Label>
                            <Input type="text" v-model="meetingForm.title" placeholder="Contoh: Pengenalan VCivic" required />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Deskripsi</Label>
                        <textarea v-model="meetingForm.description" rows="3"
                            class="flex w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#194872]"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <Button type="button" variant="outline" @click="isMeetingModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white" :disabled="meetingForm.processing">
                            Simpan Pertemuan
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Content Modal -->
        <div v-if="isContentModalOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-in zoom-in-95 duration-200">
                <h3 class="text-xl font-bold mb-4 text-slate-800">{{ isEditContentMode ? 'Edit' : 'Tambah' }} Materi Template</h3>
                <form @submit.prevent="submitContent" class="space-y-4">
                    <div class="space-y-2">
                        <Label>Tipe Materi</Label>
                        <Select v-model="contentForm.type" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Tipe" />
                            </SelectTrigger>
                            <SelectContent class="z-[200]">
                                <SelectItem value="text">Teks Deskripsi</SelectItem>
                                <SelectItem value="video">Link Video (YouTube/Drive)</SelectItem>
                                <SelectItem value="pdf">Link Dokumen (PDF)</SelectItem>
                                <SelectItem value="link">Tautan Web</SelectItem>
                                <SelectItem value="infografis">Link Gambar/Infografis</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>Judul Materi</Label>
                        <Input type="text" v-model="contentForm.title" placeholder="Contoh: Video Tutorial pengerjaan" required />
                    </div>
                    <div class="space-y-2">
                        <Label>Isi / Tautan URL</Label>
                        <textarea v-model="contentForm.content" rows="4" required
                            class="flex w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#194872]"></textarea>
                        <p class="text-[10px] text-slate-400 italic">Masukkan teks materi atau tempelkan URL (https://...) sesuai tipe yang dipilih.</p>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <Button type="button" variant="outline" @click="isContentModalOpen = false">Batal</Button>
                        <Button type="submit" class="bg-[#194872] hover:bg-[#194872]/90 text-white" :disabled="contentForm.processing">
                            Simpan Materi
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <AlertDialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
            <AlertDialogContent class="z-[110]">
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Data Template?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini akan menghapus data di Master Kurikulum secara permanen. Pengguna lain tidak akan terpengaruh sebelum mereka memilih untuk sinkronisasi ulang.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="isDeleteDialogOpen = false">Batal</AlertDialogCancel>
                    <AlertDialogAction @click="executeDelete" class="bg-red-600 text-white hover:bg-red-700">Ya, Hapus Permanen</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>
