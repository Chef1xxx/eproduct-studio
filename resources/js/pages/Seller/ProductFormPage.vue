<template>
    <PublicLayout>
        <section class="product-form-page">
            <h1 class="product-form-page__title">
                {{ product ? 'Редактировать товар' : 'Добавить товар' }}
            </h1>
            <ProductForm :product="product" :categories="categories" />
        </section>
    </PublicLayout>
</template>

<script setup lang="ts">
import ProductForm from '@/features/product-form/ui/ProductForm.vue';
import PublicLayout from '@/widgets/layout/PublicLayout.vue';

defineProps<{
    product: App.DTO.ProductDto | null;
    categories: App.DTO.CategoryDto[];
}>();
</script>

<style scoped lang="scss">
.product-form-page {
    &__title {
        margin: 0 0 1.25rem;
    }
}
</style>