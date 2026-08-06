export interface PaginationMeta {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
}

export interface PaginatedResponse<T, M extends PaginationMeta = PaginationMeta> {
    data: T[];
    meta: M;
}
