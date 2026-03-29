<script setup>
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

const form = useForm({
    username: '',
    password: '',
    role: 'admin',
    remember: false,
});

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Admin Login" />

    <div class="flex justify-center items-center h-screen bg-gradient-to-t from-[#194872] to-[#1E5F93] to-white">
        <div class="flex flex-col w-[30%] px-6 py-10 bg-white rounded-2xl shadow-xl">
            <div class="flex flex-col gap-8">
                <img src="/Logo.svg" alt="Logo" class="h-20 w-auto">

                <h2 class="text-2xl font-bold text-center mb-6">Portal Admin Vcivic</h2>
            </div>

            <form @submit.prevent="submit">

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