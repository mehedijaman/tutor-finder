import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

export function usePushNotifications() {
    const page = usePage();
    const isSubscribed = ref(false);
    const isSupported = ref(false);
    const isLoading = ref(false);
    const permission = ref<NotificationPermission>('default');

    const vapidPublicKey = computed(() => page.props.vapidPublicKey as string);

    function urlBase64ToUint8Array(base64String: string): Uint8Array {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function getCsrfToken(): string {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta?.getAttribute('content') ?? '';
    }

    async function checkSupport(): Promise<void> {
        isSupported.value = 'serviceWorker' in navigator && 'PushManager' in window;

        if (isSupported.value) {
            permission.value = Notification.permission;

            if (permission.value === 'granted') {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                isSubscribed.value = !!subscription;
            }
        }
    }

    async function subscribe(): Promise<boolean> {
        if (!isSupported.value || !vapidPublicKey.value) {
            return false;
        }

        isLoading.value = true;

        try {
            const permissionResult = await Notification.requestPermission();
            permission.value = permissionResult;

            if (permissionResult !== 'granted') {
                isLoading.value = false;
                return false;
            }

            const registration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey.value),
            });

            const subscriptionJson = subscription.toJSON();

            const response = await fetch('/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    endpoint: subscriptionJson.endpoint,
                    keys: {
                        auth: subscriptionJson.keys?.auth,
                        p256dh: subscriptionJson.keys?.p256dh,
                    },
                }),
            });

            if (response.ok) {
                isSubscribed.value = true;
                isLoading.value = false;
                return true;
            }

            isLoading.value = false;
            return false;
        } catch {
            isLoading.value = false;
            return false;
        }
    }

    async function unsubscribe(): Promise<boolean> {
        if (!isSupported.value) {
            return false;
        }

        isLoading.value = true;

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                isSubscribed.value = false;
                isLoading.value = false;
                return true;
            }

            const endpoint = subscription.endpoint;
            await subscription.unsubscribe();

            const response = await fetch('/push-subscriptions', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ endpoint }),
            });

            if (response.ok) {
                isSubscribed.value = false;
                isLoading.value = false;
                return true;
            }

            isLoading.value = false;
            return false;
        } catch {
            isLoading.value = false;
            return false;
        }
    }

    return {
        isSupported,
        isSubscribed,
        isLoading,
        permission,
        checkSupport,
        subscribe,
        unsubscribe,
    };
}
