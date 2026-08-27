<template>
    <PublicLayout>
        <section class="auth-page">
            <h1 class="auth-page__title">Вход</h1>
            <LoginForm />
            <p class="auth-page__hint">
                Нет аккаунта?
                <Link href="/register">Регистрация</Link>
            </p>
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import LoginForm from '@/features/auth/ui/LoginForm.vue';
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