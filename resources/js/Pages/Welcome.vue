<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { 
    GraduationCap, BookOpen, ShieldAlert, 
    ArrowRight, LayoutDashboard, MonitorPlay, Users,
    MessageSquare, ClipboardCheck, Link as LinkIcon, Download, Key
} from 'lucide-vue-next';
import BookVector from '../../assets/BookVector.svg';
import TogaVector from '../../assets/TogaVector.svg';

const features = [
    {
        title: 'Materi Multi-Format',
        description: 'Mendukung penyampaian materi perkuliahan dalam berbagai format mulai dari PDF, Video YouTube, hingga tautan eksternal.',
        icon: MonitorPlay,
        color: 'text-indigo-600',
        bg: 'bg-indigo-100'
    },
    {
        title: 'Forum Diskusi Real-time',
        description: 'Fasilitas tanya-jawab dan kolaborasi langsung antara Dosen dan Mahasiswa pada setiap pertemuan dengan sinkronisasi otomatis.',
        icon: MessageSquare,
        color: 'text-emerald-600',
        bg: 'bg-emerald-100'
    },
    {
        title: 'Sistem Tes Ujian Online',
        description: 'Ujian terpadu dengan grading otomatis instan, riwayat percobaan, dan umpan balik langsung setelah pengerjaan selesai.',
        icon: ClipboardCheck,
        color: 'text-amber-600',
        bg: 'bg-amber-100'
    },
    {
        title: 'Akses Kelas Kode Unik',
        description: 'Keamanan portal kelas dengan menggunakan kode akses khusus yang dilengkapi verifikasi manual dari dosen pengampu.',
        icon: Key,
        color: 'text-rose-600',
        bg: 'bg-rose-100'
    },
    {
        title: 'Master Kurikulum Sinkron',
        description: 'Sinkronisasi instan template silabus (Master Kurikulum) dari Admin Pusat ke kelas spesifik milik Dosen pengampu.',
        icon: LinkIcon,
        color: 'text-sky-600',
        bg: 'bg-sky-100'
    },
    {
        title: 'Ekspor Rekapitulasi Nilai',
        description: 'Kemudahan pelaporan akademis bagi Dosen melalui fitur ekspor seluruh nilai mahasiswa atau nilai kuis ke format spreadsheet.',
        icon: Download,
        color: 'text-violet-600',
        bg: 'bg-violet-100'
    }
];

const roles = [
    {
        title: 'Portal Mahasiswa',
        description: 'Bergabung kelas via kode akses, pelajari modul, ikuti diskusi aktif, pantau progres pembelajaran, dan kerjakan kuis evaluasi.',
        icon: GraduationCap,
    },
    {
        title: 'Manajemen Dosen',
        description: 'Setujui mahasiswa kelas, import kurikulum master, buat pertanyaan kuis pilihan ganda, dan unduh laporan hasil studi.',
        icon: BookOpen,
    },
    {
        title: 'Sistem Admin',
        description: 'Kontrol kredensial pengguna, rancang susunan materi template kurikulum, dan atur ketersediaan setiap sesi pertemuan kelas.',
        icon: ShieldAlert,
    }
];
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="min-h-screen bg-slate-50 font-sans selection:bg-[#194872] selection:text-white">
        <!-- Navigation -->
        <nav class="absolute top-0 w-full z-50 bg-transparent px-6 sm:px-10 py-6 flex justify-between items-center">
            <div>
                <img src="/Logo.svg" alt="Vcivic Logo" class="h-10 sm:h-12 w-auto object-contain brightness-0 invert">
            </div>
            <div class="flex items-center gap-4">
                <Link :href="route('login')">
                    <Button class="bg-white text-[#194872] hover:bg-slate-100 rounded-xl px-6 font-bold shadow-lg">
                        Masuk
                    </Button>
                </Link>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden bg-gradient-to-br from-[#194872] via-[#1E5F93] to-[#2b7bbd]">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full -mr-20 -mt-20 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white opacity-5 rounded-full -ml-20 -mb-20 blur-xl"></div>
            
            <div class="container mx-auto px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight mb-6">
                        Platform Pembelajaran <br class="hidden sm:block"> Terpadu dan Interaktif
                    </h1>
                    <p class="text-lg sm:text-xl text-blue-100 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Pengalaman belajar mengajar digital komprehensif mulai dari pemantauan materi, forum diskusi kelas secara real-time, hingga evaluasi tes online terpadu.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link :href="route('login')" class="w-full sm:w-auto">
                            <Button class="w-full sm:w-auto bg-white hover:bg-slate-100 text-[#194872] rounded-xl py-6 px-8 text-lg font-bold shadow-xl transition-transform hover:-translate-y-1">
                                Masuk Portal Sekarang
                                <ArrowRight class="ml-2 w-5 h-5" />
                            </Button>
                        </Link>
                        <a href="#fitur" class="w-full sm:w-auto text-white font-medium hover:text-blue-200 transition-colors py-3 px-6">
                            Pelajari Fitur Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Overview Section -->
        <div id="peran" class="py-16 bg-white border-b border-slate-100">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div v-for="role in roles" :key="role.title" class="flex flex-col items-center text-center p-6">
                        <div class="bg-slate-50 p-4 rounded-full mb-4 ring-1 ring-slate-100">
                            <component :is="role.icon" class="w-8 h-8 text-[#194872]" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ role.title }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">{{ role.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Features Highlight -->
        <div id="fitur" class="py-24 bg-slate-50">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-slate-800 mb-4">Fitur Lengkap Sivitas Akademika</h2>
                    <p class="text-slate-600 max-w-2xl mx-auto text-lg">
                        Seluruh fungsionalitas yang dirancang khusus untuk memfasilitasi kebutuhan Dosen dan Mahasiswa dalam satu ekosistem Vcivic yang terintegrasi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                    <div v-for="feature in features" :key="feature.title" class="bg-white rounded-3xl p-8 border border-slate-200 hover:shadow-xl transition-all duration-300 group hover:-translate-y-2">
                        <div :class="[feature.bg, feature.color, 'w-14 h-14 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110']">
                            <component :is="feature.icon" class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">{{ feature.title }}</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- UI Mockup Preview -->
        <div class="py-24 bg-white">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-16 max-w-6xl mx-auto">
                    <!-- Text Info -->
                    <div class="lg:w-1/2 space-y-8">
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 mb-4">Lingkungan Belajar Tersinkronisasi</h2>
                            <p class="text-slate-600 text-lg leading-relaxed mb-6">
                                Antarmuka pertemuan tatap muka digital membagi layar ke dalam Tab Materi, Forum Diskusi kelas, dan Panel Ujian interaktif. Beralih antar modul kegiatan belajar dengan mulus tanpa harus berpindah halaman.
                            </p>
                            
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3">
                                    <div class="bg-blue-100 p-1.5 rounded-full"><MessageSquare class="w-4 h-4 text-blue-600" /></div>
                                    <span class="text-slate-700 font-medium">Auto-scroll riwayat obrolan diskusi</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <div class="bg-green-100 p-1.5 rounded-full"><ClipboardCheck class="w-4 h-4 text-green-600" /></div>
                                    <span class="text-slate-700 font-medium">Validasi submission Kuis seketika</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <div class="bg-indigo-100 p-1.5 rounded-full"><MonitorPlay class="w-4 h-4 text-indigo-600" /></div>
                                    <span class="text-slate-700 font-medium">Video/Embed PDF render langsung</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Illustration/Mockup representation -->
                    <div class="lg:w-1/2 w-full">
                        <div class="relative bg-slate-50 rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform rotate-1 hover:rotate-0 transition-transform duration-500">
                            <!-- Fake UI Header Tabs -->
                            <div class="bg-white border-b border-slate-200 flex px-4 pt-4 gap-2">
                                <div class="bg-slate-100 px-4 py-2 rounded-t-lg border-x border-t border-slate-200 text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <MonitorPlay class="w-4 h-4"/> Materi
                                </div>
                                <div class="px-4 py-2 text-sm font-medium text-slate-500 flex items-center gap-2">
                                    <MessageSquare class="w-4 h-4"/> Diskusi <span class="bg-red-500 text-white text-[10px] px-1.5 rounded-full">New</span>
                                </div>
                                <div class="px-4 py-2 text-sm font-medium text-slate-500 flex items-center gap-2">
                                    <ClipboardCheck class="w-4 h-4"/> Kuis
                                </div>
                            </div>
                            
                            <!-- Fake UI Content Window -->
                            <div class="p-6">
                                <div class="w-full h-48 bg-slate-200 rounded-xl flex items-center justify-center relative overflow-hidden group">
                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition-colors"></div>
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg cursor-pointer transform group-hover:scale-110 transition-transform">
                                        <div class="w-0 h-0 border-t-8 border-t-transparent border-l-[14px] border-l-red-600 border-b-8 border-b-transparent ml-1"></div>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div class="h-4 w-3/4 bg-slate-200 rounded"></div>
                                    <div class="h-3 w-1/2 bg-slate-100 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action Footer -->
        <footer class="bg-[#194872] text-white py-16 relative overflow-hidden">
            <div class="container mx-auto px-6 lg:px-8 text-center relative z-10">
                <h2 class="text-3xl font-bold mb-6">Mulai Akses Ruang Kelas Anda</h2>
                <p class="text-blue-100 mb-10 max-w-xl mx-auto">Gunakan akun dosen atau mahasiswa yang telah diberikan untuk mengelola kurikulum dan memantau pembelajaran interaktif.</p>
                <Link :href="route('login')">
                    <Button class="bg-white hover:bg-slate-100 text-[#194872] rounded-xl px-10 py-6 text-lg font-bold shadow-lg transition-transform hover:scale-105">
                        Masuk ke Portal
                    </Button>
                </Link>
                <div class="mt-16 pt-8 border-t border-[#1E5F93] flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-2 opacity-80">
                        <img src="/Logo.svg" alt="Vcivic Logo" class="h-6 w-auto object-contain brightness-0 invert">
                        <span class="text-sm font-semibold ml-2 border-l border-blue-400 pl-4">Platform Edukasi Terpadu</span>
                    </div>
                    <div class="text-sm text-blue-200 opacity-80">
                        &copy; {{ new Date().getFullYear() }} Vcivic. Hak Cipta Dilindungi.
                    </div>
                </div>
            </div>
            
            <!-- Dekorasi SVG Halus -->
            <div class="absolute bottom-10 left-10 opacity-[0.03] w-64 h-64 pointer-events-none">
                <img :src="TogaVector" alt="Decor" class="w-full h-full object-contain">
            </div>
            <div class="absolute top-10 right-10 opacity-[0.03] w-48 h-48 pointer-events-none">
                <img :src="BookVector" alt="Decor" class="w-full h-full object-contain">
            </div>
        </footer>
    </div>
</template>