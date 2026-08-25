<script setup lang="ts">
import { MessageCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import { useSiteSettings } from '@/composables/useSiteSettings';

const { socialDetails, primaryPhone } = useSiteSettings();

const whatsappUrl = computed(() => {
    if (socialDetails.value?.whatsapp) {
        return socialDetails.value.whatsapp;
    }
    const cleanPhone = (primaryPhone.value || '+8801947368456').replace(
        /[^\d+]/g,
        '',
    );
    return `https://wa.me/${cleanPhone.replace('+', '')}`;
});
</script>

<template>
    <aside aria-label="WhatsApp Quick Support">
        <a
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Contact us on WhatsApp"
            class="group fixed right-5 bottom-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25d366] text-white shadow-2xl transition-all duration-300 hover:scale-110 hover:bg-[#20bd5a] focus:ring-4 focus:ring-green-300 focus:outline-hidden sm:right-6 sm:bottom-6 sm:h-16 sm:w-16"
        >
            <span
                class="absolute -inset-1 animate-ping rounded-full bg-[#25d366] opacity-35 group-hover:opacity-0"
            ></span>
            <MessageCircle
                class="relative h-7 w-7 fill-current sm:h-8 sm:w-8"
            />
        </a>
    </aside>
</template>
