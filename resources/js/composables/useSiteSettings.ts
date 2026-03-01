import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type SharedSiteSettings = {
    site_name: string;
    logo_url: string | null;
    primary_phone: string | null;
    primary_email: string | null;
    primary_address: string | null;
    social_details: Record<string, string>;
};

const defaultSiteSettings: SharedSiteSettings = {
    site_name: 'Tutor Finder',
    logo_url: null,
    primary_phone: null,
    primary_email: null,
    primary_address: null,
    social_details: {},
};

export function useSiteSettings() {
    const page = usePage();

    const normalizeNullableString = (value: unknown): string | null => {
        if (typeof value !== 'string') {
            return null;
        }

        const normalized = value.trim();

        return normalized !== '' ? normalized : null;
    };

    const siteSettings = computed<SharedSiteSettings>(() => {
        const rawSettings = page.props.siteSettings;

        if (! rawSettings || typeof rawSettings !== 'object') {
            return defaultSiteSettings;
        }

        const socialDetails = (rawSettings as { social_details?: unknown }).social_details;
        const normalizedSocialDetails =
            socialDetails && typeof socialDetails === 'object' && ! Array.isArray(socialDetails)
                ? (socialDetails as Record<string, string>)
                : {};

        return {
            site_name: String((rawSettings as { site_name?: unknown }).site_name ?? defaultSiteSettings.site_name),
            logo_url: normalizeNullableString((rawSettings as { logo_url?: unknown }).logo_url),
            primary_phone: normalizeNullableString((rawSettings as { primary_phone?: unknown }).primary_phone),
            primary_email: normalizeNullableString((rawSettings as { primary_email?: unknown }).primary_email),
            primary_address: normalizeNullableString((rawSettings as { primary_address?: unknown }).primary_address),
            social_details: normalizedSocialDetails,
        };
    });

    const siteName = computed(() => siteSettings.value.site_name);
    const logoUrl = computed(() => siteSettings.value.logo_url);
    const primaryPhone = computed(() => siteSettings.value.primary_phone);
    const primaryEmail = computed(() => siteSettings.value.primary_email);
    const primaryAddress = computed(() => siteSettings.value.primary_address);
    const socialDetails = computed(() => siteSettings.value.social_details);

    return {
        siteSettings,
        siteName,
        logoUrl,
        primaryPhone,
        primaryEmail,
        primaryAddress,
        socialDetails,
    };
}
