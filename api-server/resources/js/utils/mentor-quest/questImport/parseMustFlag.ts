const MUST_FALSE_VALUES = new Set([
    '-',
    '×',
    'x',
    '✗',
    'no',
    'n',
    'false',
    '0',
    'off',
    '任意',
    'optional',
]);

const MUST_TRUE_VALUES = new Set([
    '◯',
    '○',
    '〇',
    '●',
    'o',
    'yes',
    'y',
    'true',
    '1',
    'on',
    'must',
    '必須',
    '✓',
    '✔',
]);

/** CSV MUST 列 → isRequired（空欄は任意、列自体が無いときは必須扱い） */
export const parseMustFlag = (
    raw: string,
    options?: {
        columnPresent?: boolean;
    },
): boolean => {
    const trimmed = raw.trim();

    if (trimmed === '') {
        return options?.columnPresent ? false : true;
    }

    const normalized = trimmed.toLowerCase();

    if (MUST_FALSE_VALUES.has(trimmed) || MUST_FALSE_VALUES.has(normalized)) {
        return false;
    }

    if (MUST_TRUE_VALUES.has(trimmed) || MUST_TRUE_VALUES.has(normalized)) {
        return true;
    }

    return true;
};
