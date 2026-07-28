/** RFC4180 風 CSV パーサ（引用符・改行対応） */
export const parseCsvText = (text: string): string[][] => {
    const rows: string[][] = [];
    let row: string[] = [];
    let cell = '';
    let inQuotes = false;

    const pushCell = (): void => {
        row.push(cell);
        cell = '';
    };

    const pushRow = (): void => {
        if (row.length > 0 || cell !== '') {
            pushCell();
            rows.push(row);
        }
        row = [];
    };

    const normalized = text.replace(/^\uFEFF/, '');

    for (let index = 0; index < normalized.length; index += 1) {
        const char = normalized[index];
        const next = normalized[index + 1];

        if (char === '"') {
            if (inQuotes && next === '"') {
                cell += '"';
                index += 1;
            } else {
                inQuotes = !inQuotes;
            }
            continue;
        }

        if (!inQuotes && char === ',') {
            pushCell();
            continue;
        }

        if (!inQuotes && (char === '\n' || char === '\r')) {
            if (char === '\r' && next === '\n') {
                index += 1;
            }
            pushRow();
            continue;
        }

        cell += char;
    }

    if (cell !== '' || row.length > 0) {
        pushRow();
    }

    return rows.filter((cells) => cells.some((value) => value.trim() !== ''));
};
