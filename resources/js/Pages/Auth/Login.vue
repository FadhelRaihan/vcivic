<script setup>
// Formulir Login utama untuk pengguna dengan role Mahasiswa maupun Dosen.
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import BookVector from '../../../assets/BookVector.svg';
import TogaVector from '../../../assets/TogaVector.svg';

const form = useForm({
    username: '',
    password: '',
    role: '',
    remember: false,
});

const showPassword = ref(false);
// Mengubah state untuk memperlihatkan atau menyembunyikan plain text password.
const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
// Mengirim kredensial username, password, dan jenis role ke endpoint otentikasi server.
const submit = () => {
    if (!form.role) {
        form.setError('role', 'Silakan pilih masuk sebagai Dosen atau Mahasiswa.');
        return;
    }

    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Masuk" />

    <div class="flex justify-center items-center min-h-screen bg-gradient-to-t from-[#194872] to-[#1E5F93] to-white p-4">
        <div class="flex flex-col w-full max-w-md lg:max-w-lg xl:max-w-[30%] px-6 sm:px-10 py-8 sm:py-10 bg-white rounded-2xl shadow-xl">
            <div class="flex flex-col gap-6 sm:gap-8">
                <img src="/Logo.svg" alt="Logo" class="h-16 sm:h-20 w-auto object-contain">

                <h2 class="text-xl sm:text-2xl font-bold text-center mb-4 sm:mb-6">Selamat Datang di Vcivic</h2>
            </div>

            <form @submit.prevent="submit">

                <div class="flex flex-row gap-3 sm:gap-4 mb-2">
                    <div @click="form.role = 'dosen'; form.clearErrors('role')"
                        :class="['flex-1 items-center justify-center cursor-pointer border-2 border-[#194872]/20 p-3 sm:p-4 rounded-2xl flex flex-col transition-all',
                            form.role === 'dosen' ? 'border-[#194872] bg-[#194872]/20 shadow-sm' : 'border-[#194872]/30 hover:border-[#194872]']">
                        <img :src="BookVector" alt="BookVector" class="h-[1.5rem] sm:h-[2rem] mb-2">
                        <p class="text-xs sm:text-sm text-center">Sebagai<br><b class="text-gray-800">DOSEN</b></p>
                    </div>

                    <div @click="form.role = 'mahasiswa'; form.clearErrors('role')"
                        :class="['flex-1 items-center justify-center cursor-pointer border-2 border-[#194872]/20 p-3 sm:p-4 rounded-2xl flex flex-col transition-all',
                            form.role === 'mahasiswa' ? 'border-[#194872] bg-[#194872]/20 shadow-sm' : 'border-[#194872]/30 hover:border-[#194872]']">
                        <img :src="TogaVector" alt="TogaVector" class="h-[1.5rem] sm:h-[2rem] mb-2">
                        <p class="text-xs sm:text-sm text-center">Sebagai<br><b class="text-gray-800">MAHASISWA</b></p>
                    </div>
                </div>

                <div v-if="form.errors.role" class="text-sm text-red-500 mb-4 text-center">
                    {{ form.errors.role }}
                </div>

                <div class="mt-4">
                    <Label for="username">Username</Label>
                    <Input id="username" type="text" v-model="form.username" class="mt-1 block w-full" required
                        autofocus autocomplete="username" />
                    <div v-if="form.errors.username" class="text-sm text-red-500 mt-1">{{ form.errors.username }}</div>
                </div>

                <div class="mt-4">
                    <Label for="password">Password</Label>

                    <div class="relative mt-1">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password"
                            class="block w-full pr-10" required autocomplete="current-password" />

                        <button type="button" @click="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <div v-if="form.errors.password" class="text-sm text-red-500 mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="mt-6">
                    <Button class="w-full bg-[#194872] hover:bg-[#194872]/80 text-white rounded-xl py-6 text-lg"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        MASUK
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>