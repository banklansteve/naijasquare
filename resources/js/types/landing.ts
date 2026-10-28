import type { IconName } from '@/types/icons';

export interface PublicCommunity {
    name: string;
    href: string;
}

export interface BannerAside {
    title: string;
    body: string;
    image: string;
}

export interface BoardCard {
    category: string;
    place: string;
    kind: string;
    line: string;
    detail: string;
}

export type DoorwayIllustration = 'table' | 'doorway' | 'meal' | 'handshake';

export interface DoorwayCard {
    illustration: DoorwayIllustration;
    title: string;
    body: string;
    tags: string[];
}

export interface DoHere {
    heading: string;
    subline: string;
    cards: DoorwayCard[];
    cta: string;
    note: string;
    signupHref: string;
}

export interface HowStep {
    icon: IconName;
    title: string;
    body: string;
}

export interface TrustPromise {
    icon: IconName;
    title: string;
    body: string;
}

export interface Faq {
    question: string;
    answer: string;
}

export interface FooterLink {
    label: string;
    href: string;
}

export interface FounderNote {
    name: string | null;
    paragraphs: string[];
}

export interface Invite {
    inviter: {
        name: string;
        member_since: string;
        email_verified: boolean;
    };
    community: {
        name: string;
        area: string;
        requires_approval: boolean;
    };
    note: string | null;
}
