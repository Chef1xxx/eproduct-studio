<template>
    <PublicLayout>
        <section class="auth-page">
            <h1 class="auth-page__title">Регистрация</h1>
            <RegisterForm />
            <p class="auth-page__hint">
                Уже есть аккаунт?
                <Link href="/login">Вход</Link>
            </p>
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import RegisterForm from '@/features/auth/ui/RegisterForm.vue';
import PublicLayout from '@/widgets/layout/PublicLayout.vue';
</script>

<style scoped lang="scss">
.auth-page {
    &__title {
        margin: 0 0 1.25rem;
    }

    &__hint {
        margin-top: 1rem;
    }
}
</style>