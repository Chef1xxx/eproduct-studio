<template>
    <div class="public-layout">
        <header class="public-layout__header">
            <Link href="/" class="public-layout__brand">eProduct Studio</Link>
        </header>

        <main class="public-layout__main">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<style scoped lang="scss">
.public-layout {
    min-height: 100vh;

    &__header {
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

    &__main {
        padding: 2rem;
        max-width: 1100px;
        margin: 0 auto;
    }
}
</style>