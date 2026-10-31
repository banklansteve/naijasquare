export interface PublicCommunity {
    name: string;
    href: string;
}

export type ValueIcon = 'match' | 'help' | 'trust';

export interface ValuePoint {
    icon: ValueIcon;
    title: string;
    body: string;
}

export interface ValueSection {
    heading: string;
    subline: string;
    points: ValuePoint[];
}

export interface BannerActions {
    primary: string;
    secondary: string;
    primaryHref: string;
}

export interface CloseBand {
    line: string;
    cta: string;
    ctaHref: string;
    account: string;
    signIn: string;
    signInHref: string;
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
    signupHref: string;
}

export interface ProfileScreen {
    type: 'profile';
    label: string;
    nameLabel: string;
    nameValue: string;
    interests: { name: string; selected: boolean }[];
    privacy: string;
    detail: string;
}

export interface CommunityScreen {
    type: 'communities';
    query?: string;
    groups: { name: string; access: string; open: boolean }[];
}

export interface ThreadScreen {
    type: 'thread';
    tag: string;
    line: string;
    replyLabel: string;
    reply: string;
    status: string;
}

export type HowScreen = ProfileScreen | CommunityScreen | ThreadScreen;

export interface HowStep {
    title: string;
    body: string;
    screen: HowScreen;
}

export interface HowItWorks {
    heading: string;
    subline: string;
    steps: HowStep[];
    cta: string;
    note: string;
    signupHref: string;
}

export interface TrustNotice {
    line: string;
    meta: string;
    status: string;
}

export interface TrustPrivacy {
    label: string;
    level: string;
    place: string;
}

export interface TrustPromise {
    title: string;
    body: string;
    notice?: TrustNotice;
    badge?: string;
    modes?: string[];
    privacy?: TrustPrivacy;
}

export interface TrustSection {
    heading: string;
    subline: string;
    promises: TrustPromise[];
}

export interface Faq {
    question: string;
    answer: string;
}

export interface FaqSection {
    heading: string;
    subline: string;
    items: Faq[];
}

export interface FooterLink {
    label: string;
    href: string;
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
