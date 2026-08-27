<template>
    <Button label="Выйти" severity="secondary" :loading="form.processing" @click="logout" />
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';

const form = useForm({});

function logout(): void {
    form.post('/logout');
}
</script>