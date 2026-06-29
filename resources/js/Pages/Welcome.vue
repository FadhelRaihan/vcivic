<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { 
    GraduationCap, BookOpen, ShieldAlert, 
    ArrowRight, LayoutDashboard, MonitorPlay, Users 
} from 'lucide-vue-next';
import BookVector from '../../assets/BookVector.svg';
import TogaVector from '../../assets/TogaVector.svg';

const features = [
    {
        title: 'Portal Mahasiswa',
        description: 'Akses modul materi, kerjakan kuis, dan pantau progres pembelajaran dengan mudah di satu tempat.',
        icon: GraduationCap,
        color: 'text-green-600',
        bg: 'bg-green-100'
    },
    {
        title: 'Manajemen Kelas Dosen',
        description: 'Kelola kelas, sinkronisasi kurikulum, evaluasi kuis, dan pantau aktivitas peserta didik.',
        icon: BookOpen,
        color: 'text-blue-600',
        bg: 'bg-blue-100'
    },
    {
        title: 'Sistem Admin Pusat',
        description: 'Kontrol penuh pengguna, pengelolaan master kurikulum, dan pengaturan pertemuan kelas.',
        icon: ShieldAlert,
        color: 'text-red-600',
        bg: 'bg-red-100'
    }
];

const previews = [
    {
        title: 'Pemantauan Progres Belajar',
        description: 'Mahasiswa dapat dengan mudah melihat capaian pembelajaran dan indikator penyelesaian setiap pertemuan.',
        icon: MonitorPlay
    },
    {
        title: 'Manajemen Kurikulum Terpadu',
        description: 'Dosen dapat mensinkronkan kurikulum utama dengan kelas yang sedang berlangsung secara real-time.',
        icon: LayoutDashboard
    },
    {
        title: 'Aksesibilitas yang Mudah',
        description: 'Platform yang didesain secara bersih dan modern memudahkan pengguna dalam berinteraksi.',
        icon: Users
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
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full -mr-20 -mt-20 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white opacity-5 rounded-full -ml-20 -mb-20 blur-xl"></div>
            
            <div class="container mx-auto px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight mb-6">
                        Platform Pembelajaran <br class="hidden sm:block"> Terpadu dan Interaktif
                    </h1>
                    <p class="text-lg sm:text-xl text-blue-100 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Tingkatkan pengalaman belajar mengajar dengan antarmuka yang bersih, terstruktur, dan mudah diakses oleh mahasiswa maupun dosen.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link :href="route('login')" class="w-full sm:w-auto">
                            <Button class="w-full sm:w-auto bg-white hover:bg-slate-100 text-[#194872] rounded-xl py-6 px-8 text-lg font-bold shadow-xl transition-transform hover:-translate-y-1">
                                Mulai Belajar Sekarang
                                <ArrowRight class="ml-2 w-5 h-5" />
                            </Button>
                        </Link>
                        <a href="#fitur" class="w-full sm:w-auto text-white font-medium hover:text-blue-200 transition-colors py-3 px-6">
                            Pelajari Fitur
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="fitur" class="py-20 bg-white">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-slate-800 mb-4">Satu Platform, Tiga Peran Utama</h2>
                    <p class="text-slate-600 max-w-2xl mx-auto">
                        Dirancang khusus untuk memfasilitasi kebutuhan seluruh sivitas akademika dalam satu ekosistem yang terintegrasi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div v-for="feature in features" :key="feature.title" class="bg-slate-50 rounded-3xl p-8 border border-slate-100 hover:shadow-xl transition-all duration-300 group hover:-translate-y-2">
                        <div :class="[feature.bg, feature.color, 'w-16 h-16 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110']">
                            <component :is="feature.icon" class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">{{ feature.title }}</h3>
                        <p class="text-slate-600 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="py-20 bg-slate-50">
            <div class="container mx-auto px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-16 max-w-6xl mx-auto">
                    <!-- Text Info -->
                    <div class="lg:w-1/2 space-y-8">
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 mb-4">Fokus Pada Pengalaman Belajar</h2>
                            <p class="text-slate-600 text-lg leading-relaxed">
                                Antarmuka Vcivic dirancang untuk meminimalkan gangguan visual sehingga Anda bisa berfokus pada materi pembelajaran dan interaksi kelas.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div v-for="preview in previews" :key="preview.title" class="flex gap-4">
                                <div class="shrink-0 mt-1">
                                    <div class="bg-[#194872]/10 p-2 rounded-lg">
                                        <component :is="preview.icon" class="w-6 h-6 text-[#194872]" />
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 mb-1">{{ preview.title }}</h4>
                                    <p class="text-slate-600 text-sm">{{ preview.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Illustration/Mockup representation -->
                    <div class="lg:w-1/2 w-full">
                        <div class="relative bg-white rounded-3xl shadow-2xl p-6 border border-slate-200 overflow-hidden transform rotate-2 hover:rotate-0 transition-transform duration-500">
                            <!-- Fake UI Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-[#194872] rounded-full flex items-center justify-center">
                                        <GraduationCap class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <div class="h-4 w-32 bg-slate-200 rounded animate-pulse"></div>
                                        <div class="h-3 w-24 bg-slate-100 rounded mt-2 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Fake UI Content -->
                            <div class="space-y-4">
                                <div class="h-24 bg-slate-50 rounded-2xl border border-slate-100 flex items-center p-4">
                                   <div class="h-12 w-12 rounded-full border-4 border-green-500 mr-4"></div>
                                   <div class="space-y-2 flex-1">
                                       <div class="h-4 w-1/3 bg-slate-200 rounded animate-pulse"></div>
                                       <div class="h-3 w-1/2 bg-slate-100 rounded animate-pulse"></div>
                                   </div>
                                </div>
                                <div class="h-20 bg-slate-50 rounded-2xl border border-slate-100 p-4">
                                    <div class="h-4 w-full bg-slate-200 rounded mb-2 animate-pulse"></div>
                                    <div class="h-4 w-2/3 bg-slate-200 rounded animate-pulse"></div>
                                </div>
                            </div>
                            
                            <div class="absolute -bottom-6 -right-6 opacity-10 w-40 h-40">
                                <img :src="BookVector" alt="Decor" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action Footer -->
        <footer class="bg-[#194872] text-white py-12 relative overflow-hidden">
            <div class="container mx-auto px-6 lg:px-8 text-center relative z-10">
                <h2 class="text-2xl font-bold mb-6">Siap untuk memulai perjalanan akademik Anda?</h2>
                <Link :href="route('login')">
                    <Button class="bg-white hover:bg-slate-100 text-[#194872] rounded-xl px-8 py-6 text-lg font-bold shadow-lg">
                        Gabung ke Portal
                    </Button>
                </Link>
                <div class="mt-12 text-sm text-blue-200 opacity-80 pt-8 border-t border-blue-800">
                    &copy; {{ new Date().getFullYear() }} Vcivic. Hak Cipta Dilindungi.
                </div>
            </div>
            
            <div class="absolute bottom-0 left-10 opacity-5 w-64 h-64">
                <img :src="TogaVector" alt="Decor" class="w-full h-full object-contain">
            </div>
        </footer>
    </div>
</template>