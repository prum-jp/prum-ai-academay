const EXPLICIT_TOOL_SEPARATORS = /[,、/／|\n|・]+/;

/** 例: "Gemini Googleスプレッドシート" → 英字ツール名 + 日本語ツール名 */
const LATIN_THEN_JP_SEPARATOR = /\s+(?=[A-Z][a-zA-Z0-9]*[\u3000-\u9fff])/;

/**
 * CSV のツール列から AI ツール名を分割する。
 * カンマ等の明示区切りに加え、「Gemini Googleスプレッドシート」のような
 * 英字名 + 日本語名の空白区切りも 2 件として扱う。
 */
export const splitToolNames = (raw: string): string[] => {
    const trimmed = raw.trim();
    if (trimmed === '') {
        return [];
    }

    const names = trimmed
        .split(EXPLICIT_TOOL_SEPARATORS)
        .flatMap((segment) => {
            const part = segment.trim();
            if (part === '') {
                return [];
            }

            const latinJpParts = part
                .split(LATIN_THEN_JP_SEPARATOR)
                .map((value) => value.trim())
                .filter(Boolean);

            return latinJpParts.length > 0 ? latinJpParts : [part];
        })
        .filter(Boolean);

    return [...new Set(names)];
};

export const pickFirstToolName = (raw: string): string => splitToolNames(raw)[0] ?? '';
