declare namespace App {
namespace DTO {
export type CategoryDto = {
readonly id: number,
readonly name: string,
readonly slug: string,
};
export type ProductData = {
readonly name: string,
readonly price: string | number,
readonly category_id: number,
readonly short_description: string | null,
readonly description: string | null,
readonly advantages: string | null,
readonly image: undefined | null,
};
export type ProductDto = {
readonly id: number,
readonly name: string,
readonly price: string,
readonly short_description: string | null,
readonly description: string | null,
readonly advantages: string[] | null,
readonly image_url: string | null,
readonly thumbnail_url: string | null,
readonly category: App.DTO.CategoryDto | null,
readonly created_at: string | null,
};
export type UserDto = {
readonly id: number,
readonly name: string,
readonly email: string,
};
}
}
declare namespace Illuminate {
export type CursorPaginator<TKey, TValue> = {
data: TKey extends string ? Record<TKey, TValue> : TValue[],
links: {
url: string | null,
label: string,
active: boolean,
}[],
meta: {
path: string,
per_page: number,
next_cursor: string | null,
next_page_url: string | null,
prev_cursor: string | null,
prev_page_url: string | null,
},
};
export type CursorPaginatorInterface<TKey, TValue> = Illuminate.CursorPaginator<TKey, TValue>;
export type LengthAwarePaginator<TKey, TValue> = {
data: TKey extends string ? Record<TKey, TValue> : TValue[],
links: {
url: string | null,
label: string,
active: boolean,
}[],
meta: {
total: number,
current_page: number,
first_page_url: string,
from: number | null,
last_page: number,
last_page_url: string,
next_page_url: string | null,
path: string,
per_page: number,
prev_page_url: string | null,
to: number | null,
},
};
export type LengthAwarePaginatorInterface<TKey, TValue> = Illuminate.LengthAwarePaginator<TKey, TValue>;
}
declare namespace Spatie {
namespace LaravelData {
export type CursorPaginatedDataCollection<TKey, TValue> = Illuminate.CursorPaginator<TKey, TValue>;
export type PaginatedDataCollection<TKey, TValue> = Illuminate.LengthAwarePaginator<TKey, TValue>;
}
}
