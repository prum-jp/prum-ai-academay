export type QuestSheetBodySegment =
    | { type: 'text'; value: string }
    | { type: 'link'; label: string; href: string };

const MARKDOWN_LINK_PATTERN = /\[([^\]]+)\]\(([^)\s]+)\)/g;
const BARE_URL_PATTERN = /https?:\/\/[^\s<>\[\]()]+/g;
const SPREADSHEET_HYPERLINK_PATTERN =
    /=HYPERLINK\s*\(\s*"([^"]+)"\s*[;,]\s*"([^"]+)"\s*\)/gi;

const isSafeHref = (href: string): boolean => /^https?:\/\//i.test(href.trim());

const escapeRegExp = (value: string): string => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/**
 * Google スプレッドシートの HYPERLINK 数式が CSV に文字列として残った場合に Markdown 形式へ変換する。
 * 通常の CSV ダウンロードでは URL は失われ、表示テキストだけが残る。
 */
export const normalizeSpreadsheetHyperlinks = (text: string): string =>
    text.replace(SPREADSHEET_HYPERLINK_PATTERN, '[$2]($1)');

/**
 * スプレッドシート由来の「【リンク】」見出しや、
 * `[表示名](URL)` と重複する表示名行を除き、名前付きリンクだけ残す。
 */
export const normalizeQuestSheetBodyText = (text: string): string => {
    let normalized = normalizeSpreadsheetHyperlinks(text);
    normalized = normalized.replace(/^【リンク】\s*$/gm, '');
    normalized = normalized.replace(/^【リンク】\s*/gm, '');

    for (const match of normalized.matchAll(MARKDOWN_LINK_PATTERN)) {
        const label = match[1]?.trim() ?? '';
        if (label === '') {
            continue;
        }

        const duplicateLinePattern = new RegExp(`^\\s*${escapeRegExp(label)}\\s*$\\n?`, 'gm');
        normalized = normalized.replace(duplicateLinePattern, '');
    }

    return normalized.replace(/\n{3,}/g, '\n\n').trim();
};

export const parseQuestSheetBody = (text: string): QuestSheetBodySegment[] => {
    const normalized = normalizeQuestSheetBodyText(text);
    const pattern = new RegExp(
        `${MARKDOWN_LINK_PATTERN.source}|${BARE_URL_PATTERN.source}`,
        'g',
    );
    const segments: QuestSheetBodySegment[] = [];
    let lastIndex = 0;

    for (const match of normalized.matchAll(pattern)) {
        const index = match.index ?? 0;

        if (index > lastIndex) {
            segments.push({
                type: 'text',
                value: normalized.slice(lastIndex, index),
            });
        }

        if (match[0].startsWith('[')) {
            const href = match[2]?.trim() ?? '';
            if (isSafeHref(href)) {
                segments.push({
                    type: 'link',
                    label: match[1] ?? href,
                    href,
                });
            } else {
                segments.push({ type: 'text', value: match[0] });
            }
        } else {
            const href = match[0].trim();
            segments.push({
                type: 'link',
                label: href,
                href,
            });
        }

        lastIndex = index + match[0].length;
    }

    if (lastIndex < normalized.length) {
        segments.push({
            type: 'text',
            value: normalized.slice(lastIndex),
        });
    }

    if (segments.length === 0) {
        return [{ type: 'text', value: normalized }];
    }

    return segments;
};

export const hasQuestSheetBodyLinks = (text: string): boolean =>
    parseQuestSheetBody(text).some((segment) => segment.type === 'link');
