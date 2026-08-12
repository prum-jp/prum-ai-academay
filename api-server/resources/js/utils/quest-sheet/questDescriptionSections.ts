export interface QuestDescriptionSections {
    overview: string;
    purpose: string;
    deliverable: string;
    completionCondition: string;
}

export const PURPOSE_MARKER = '【目的】';
const PROCEDURE_MARKER = '【内容・進め方】';
const DELIVERABLE_MARKER = '【提出物】';

export const createEmptyQuestDescriptionSections = (): QuestDescriptionSections => ({
    overview: '',
    purpose: '',
    deliverable: '',
    completionCondition: '',
});

const splitDeliverable = (text: string): { completionCondition: string; deliverable: string } => {
    const index = text.indexOf(DELIVERABLE_MARKER);

    if (index === -1) {
        return { completionCondition: text.trim(), deliverable: '' };
    }

    return {
        completionCondition: text.slice(0, index).trim(),
        deliverable: text.slice(index + DELIVERABLE_MARKER.length).trim(),
    };
};

const mergeOverviewWithLegacyProcedure = (overview: string, procedure: string): string => {
    const overviewText = overview.trim();
    const procedureText = procedure.trim();

    if (procedureText === '') {
        return overviewText;
    }

    if (overviewText === '') {
        return procedureText;
    }

    return `${overviewText}\n\n${procedureText}`;
};

const parseDescriptionBody = (
    description: string,
): Pick<QuestDescriptionSections, 'overview' | 'purpose'> => {
    const text = description ?? '';
    const purposeIndex = text.indexOf(PURPOSE_MARKER);
    const procedureIndex = text.indexOf(PROCEDURE_MARKER);

    if (purposeIndex === -1 && procedureIndex === -1) {
        return {
            overview: text.trim(),
            purpose: '',
        };
    }

    const markers = [
        { index: purposeIndex, marker: PURPOSE_MARKER, key: 'purpose' as const },
        { index: procedureIndex, marker: PROCEDURE_MARKER, key: 'procedure' as const },
    ]
        .filter((item) => item.index !== -1)
        .sort((left, right) => left.index - right.index);

    let overview = text.slice(0, markers[0].index).trim();
    let purpose = '';
    let procedure = '';

    markers.forEach((item, markerIndex) => {
        const contentStart = item.index + item.marker.length;
        const contentEnd =
            markerIndex + 1 < markers.length ? markers[markerIndex + 1].index : text.length;
        const sectionContent = text.slice(contentStart, contentEnd).trim();

        if (item.key === 'purpose') {
            purpose = sectionContent;
            return;
        }

        procedure = sectionContent;
    });

    return {
        overview: mergeOverviewWithLegacyProcedure(overview, procedure),
        purpose,
    };
};

export const parseQuestDescriptionSections = (
    description: string,
    clearCondition: string,
): QuestDescriptionSections => {
    const parsedDescription = parseDescriptionBody(description ?? '');
    const { completionCondition, deliverable } = splitDeliverable(clearCondition ?? '');

    return {
        ...parsedDescription,
        deliverable,
        completionCondition,
    };
};

export const serializeQuestDescriptionSections = (
    sections: QuestDescriptionSections,
): { description: string; clearCondition: string } => {
    const descriptionParts: string[] = [];

    if (sections.overview.trim()) {
        descriptionParts.push(sections.overview.trim());
    }

    if (sections.purpose.trim()) {
        descriptionParts.push(`${PURPOSE_MARKER}\n${sections.purpose.trim()}`);
    }

    let clearCondition = sections.completionCondition.trim();

    if (sections.deliverable.trim()) {
        clearCondition = clearCondition
            ? `${clearCondition}\n${DELIVERABLE_MARKER}\n${sections.deliverable.trim()}`
            : `${DELIVERABLE_MARKER}\n${sections.deliverable.trim()}`;
    }

    return {
        description: descriptionParts.join('\n\n'),
        clearCondition,
    };
};
