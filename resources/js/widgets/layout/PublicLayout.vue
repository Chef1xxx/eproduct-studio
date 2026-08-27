<template>
    <div class="public-layout">
        <header class="public-layout__header">
            <Link href="/" class="public-layout__brand">eProduct Studio</Link>

            <nav class="public-layout__nav">
                <template v-if="user">
                    <Link href="/seller">Кабинет</Link>
                    <span class="public-layout__user">{{ user.name }}</span>
                    <LogoutButton />
                </template>
                <template v-else>
                    <Link href="/login">Вход</Link>
                    <Link href="/register">Регистрация</Link>
                </template>
            </nav>
        </header>

        <main class="public-layout__main">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import LogoutButton from '@/features/auth/ui/LogoutButton.vue';

const page = usePage();

const user = computed(() => {
    const auth = page.props.auth as { user: App.DTO.UserDto | null } | undefined;

    return auth?.user ?? null;
});
</script>

<style scoped lang="scss">
.public-layout {
    min-height: 100vh;

    &__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 2rem;
        border-bottom: 1px solid #d9e2ec;
        background: #fff;
    }

    &__brand {
        font-size: 1.25rem;
        font-weight: 700;
        color: #243b53;
        text-decoration: none;
    }

    &__nav {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    &__user {
        color: #486581;
    }

    &__main {
        padding: 2rem;
        max-width: 1100px;
        margin: 0 auto;
    }
}
</style>