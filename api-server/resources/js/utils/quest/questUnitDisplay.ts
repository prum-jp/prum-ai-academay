const formatUnitCode = (sortOrder: number): string =>
    `UNIT ${String(sortOrder).padStart(2, '0')}`;

export const formatUnitDisplayTitle = (sortOrder: number, title: string): string =>
    `${formatUnitCode(sortOrder)}：${title}`;
