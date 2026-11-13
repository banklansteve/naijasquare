/**
 * Primary help-with chips shown first on Home. Everything else lives behind "More".
 * Keys must exist in config/posts.php categories.
 */
export const PRIMARY_HELP_CATEGORY_KEYS = [
    'housing',
    'jobs',
    'immigration',
    'money',
    'education',
    'healthcare',
    'mentoring',
    'everyday',
] as const;

export type HelpCategoryOption = {
    key: string;
    label: string;
};

export function primaryHelpOptions(
    options: HelpCategoryOption[],
    selectedKeys: string[] = [],
): HelpCategoryOption[] {
    const byKey = new Map(options.map((option) => [option.key, option]));
    const primary = PRIMARY_HELP_CATEGORY_KEYS
        .map((key) => byKey.get(key))
        .filter((option): option is HelpCategoryOption => option !== undefined);

    const extras = selectedKeys
        .filter((key) => ! (PRIMARY_HELP_CATEGORY_KEYS as readonly string[]).includes(key))
        .map((key) => byKey.get(key))
        .filter((option): option is HelpCategoryOption => option !== undefined);

    return [...primary, ...extras];
}
