<template>
    <div class="product-grid">
        <ProductCard
            v-for="product in products"
            :key="product.id"
            :product="product"
        />
    </div>
</template>

<script setup lang="ts">
import ProductCard from '@/entities/product/ui/ProductCard.vue';

defineProps<{
    products: App.DTO.ProductDto[];
}>();
</script>

<style scoped lang="scss">
.product-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.5rem;
}
</style>