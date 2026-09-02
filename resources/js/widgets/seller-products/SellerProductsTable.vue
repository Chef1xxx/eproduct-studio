<template>
    <DataTable :value="products" class="seller-products-table">
        <Column header="Фото">
            <template #body="{ data }">
                <div class="seller-products-table__thumb">
                    <img
                        v-if="data.thumbnail_url || data.image_url"
                        :src="data.thumbnail_url || data.image_url"
                        :alt="data.name"
                    />
                    <span v-else>Нет фото</span>
                </div>
            </template>
        </Column>
        <Column field="name" header="Название" />
        <Column header="Категория">
            <template #body="{ data }">
                {{ data.category?.name ?? '—' }}
            </template>
        </Column>
        <Column header="Цена">
            <template #body="{ data }">
                {{ data.price }} ₽
            </template>
        </Column>
        <Column field="created_at" header="Создан" />
        <Column header="Действия">
            <template #body="{ data }">
                <div class="seller-products-table__actions">
                    <Link :href="`/seller/products/${data.id}/edit`">
                        <Button label="Изменить" size="small" text />
                    </Link>
                    <Button
                        label="Удалить"
                        size="small"
                        severity="danger"
                        text
                        @click="destroy(data.id)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>
</template>

<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

defineProps<{
    products: App.DTO.ProductDto[];
}>();

function destroy(id: number): void {
    if (!window.confirm('Удалить товар?')) {
        return;
    }

    router.delete(`/seller/products/${id}`);
}
</script>

<style scoped lang="scss">
.seller-products-table {
    &__thumb {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        background: #e4e7eb;
        font-size: 0.7rem;
        color: #829ab1;
        overflow: hidden;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    &__actions {
        display: flex;
        gap: 0.25rem;
    }
}
</style>