<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Masuk - VCivic" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-sm overflow-hidden sm:rounded-xl border border-slate-200">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-700">VCivic</h1>
                <p class="text-slate-500 text-sm mt-1">Platform Literasi Kewarganegaraan Digital</p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" autofocus autocomplete="username" />
                    <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="password">Kata Sandi</Label>
                    <Input id="password" type="password" v-model="form.password" autocomplete="current-password" />
                    <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center space-x-2 mt-4">
                    <Checkbox id="remember" :checked="form.remember" @update:checked="form.remember = $event" />
                    <Label for="remember"
                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                        Ingat Saya
                    </Label>
                </div>

                <div class="pt-2">
                    <Button type="submit" class="w-full" :disabled="form.processing">
                        MASUK
                    </Button>
                </div>
            </form>

        </div>
    </div>
</template>