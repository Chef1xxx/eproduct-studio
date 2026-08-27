<template>
    <form class="register-form" @submit.prevent="submit">
        <div class="register-form__field">
            <label for="name">Имя</label>
            <InputText id="name" v-model="form.name" class="w-full" />
            <small v-if="form.errors.name" class="register-form__error">{{ form.errors.name }}</small>
        </div>

        <div class="register-form__field">
            <label for="email">Email</label>
            <InputText id="email" v-model="form.email" type="email" class="w-full" />
            <small v-if="form.errors.email" class="register-form__error">{{ form.errors.email }}</small>
        </div>

        <div class="register-form__field">
            <label for="password">Пароль</label>
            <InputText id="password" v-model="form.password" type="password" class="w-full" />
            <small v-if="form.errors.password" class="register-form__error">{{ form.errors.password }}</small>
        </div>

        <div class="register-form__field">
            <label for="password_confirmation">Подтверждение пароля</label>
            <InputText
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="w-full"
            />
        </div>

        <Button type="submit" label="Зарегистрироваться" :loading="form.processing" />
    </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/register');
}
</script>

<style scoped lang="scss">
.register-form {
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