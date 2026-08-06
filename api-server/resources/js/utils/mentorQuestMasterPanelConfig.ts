import { mentorQuestMasterPageConfig } from '@/constants/questMaster';

export function mentorQuestMasterPanelConfig(
    suffix: string,
    icon: string,
): { title: string; icon: string } {
    return {
        title: mentorQuestMasterPageConfig.title.replace('マスタ', suffix),
        icon,
    };
}
