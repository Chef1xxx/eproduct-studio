<template>
    <form class="login-form" @submit.prevent="submit">
        <div class="login-form__field">
            <label for="email">Email</label>
            <InputText id="email" v-model="form.email" type="email" class="w-full" />
            <small v-if="form.errors.email" class="login-form__error">{{ form.errors.email }}</small>
        </div>

        <div class="login-form__field">
            <label for="password">Пароль</label>
            <InputText id="password" v-model="form.password" type="password" class="w-full" />
            <small v-if="form.errors.password" class="login-form__error">{{ form.errors.password }}</small>
        </div>

        <Button type="submit" label="Войти" :loading="form.processing" />
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const form = useForm({
    email: '',
    password: '',
});

function submit(): void {
    form.post('/login');
}
</script>

<style scoped lang="scss">
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 360px;

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    &__error {
        color: #c81e1e;
    }
}
</style>