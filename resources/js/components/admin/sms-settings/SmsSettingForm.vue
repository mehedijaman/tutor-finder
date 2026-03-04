<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    action: {
        type: String,
        required: true,
    },
    method: {
        type: String,
        default: 'post',
    },
    submitLabel: {
        type: String,
        required: true,
    },
    cancelHref: {
        type: String,
        default: '/settings/sms',
    },
    providers: {
        type: Array,
        default: () => [],
    },
    initial: {
        type: Object,
        default: () => ({
            name: '',
            provider: '',
            is_default: false,
            is_active: true,
            credentials: {},
        }),
    },
});

const toCredentialItems = (credentials) => {
    if (!credentials || typeof credentials !== 'object') {
        return [];
    }

    return Object.entries(credentials).map(([key, value]) => ({
        key: String(key),
        value: value === null || value === undefined ? '' : String(value),
    }));
};

const fallbackProvider = props.providers[0]?.name ?? '';

const form = useForm({
    name: props.initial.name ?? '',
    provider: props.initial.provider ?? fallbackProvider,
    is_default: Boolean(props.initial.is_default ?? false),
    is_active: Boolean(props.initial.is_active ?? true),
    credential_items: toCredentialItems(props.initial.credentials),
});

const selectedProvider = computed(() => {
    return (
        props.providers.find((provider) => provider.name === form.provider) ??
        null
    );
});

const selectedProviderFieldMap = computed(() => {
    return Object.fromEntries(
        (selectedProvider.value?.fields ?? []).map((field) => [
            field.key,
            field,
        ]),
    );
});

const selectedProviderFieldKeys = computed(() => {
    return (selectedProvider.value?.fields ?? []).map((field) => field.key);
});

const requiredKeysSummary = computed(() => {
    return selectedProvider.value?.required_fields?.join(', ') ?? '';
});

const synchronizeCredentialItems = () => {
    const schemaKeys = selectedProviderFieldKeys.value;
    const existingMap = Object.fromEntries(
        (form.credential_items ?? []).map((item) => [
            String(item?.key ?? ''),
            item?.value === null || item?.value === undefined
                ? ''
                : String(item.value),
        ]),
    );

    const schemaItems = schemaKeys.map((key) => ({
        key,
        value: existingMap[key] ?? '',
    }));

    const customItems = (form.credential_items ?? []).filter((item) => {
        const key = String(item?.key ?? '');

        return key !== '' && !schemaKeys.includes(key);
    });

    form.credential_items = [...schemaItems, ...customItems];
};

watch(
    () => form.provider,
    () => {
        synchronizeCredentialItems();
    },
    { immediate: true },
);

const addCustomCredential = () => {
    form.credential_items.push({
        key: '',
        value: '',
    });
};

const removeCredential = (index) => {
    form.credential_items.splice(index, 1);
};

const isSchemaCredentialKey = (key) => {
    return selectedProviderFieldKeys.value.includes(String(key));
};

const submit = () => {
    const payload = {
        name: form.name,
        provider: form.provider,
        is_default: form.is_default,
        is_active: form.is_active,
        credential_items: form.credential_items,
    };

    if (props.method.toLowerCase() === 'put') {
        form.transform(() => payload).put(props.action, {
            preserveScroll: true,
        });

        return;
    }

    form.transform(() => payload).post(props.action, {
        preserveScroll: true,
    });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="name">Gateway Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Primary OTP Gateway"
                    required
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="provider">Provider</Label>
                <select
                    id="provider"
                    v-model="form.provider"
                    required
                    class="h-10 rounded-md border px-3 text-sm"
                >
                    <option value="" disabled>Select provider</option>
                    <option
                        v-for="provider in providers"
                        :key="provider.name"
                        :value="provider.name"
                    >
                        {{ provider.name }}
                    </option>
                </select>
                <InputError :message="form.errors.provider" />
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4 text-sm text-slate-600"
        >
            <p class="font-medium text-foreground">Credential Requirements</p>
            <p v-if="selectedProvider">
                Required: {{ requiredKeysSummary || 'None' }}.
            </p>
            <p v-else>Select a provider to view required credentials.</p>
            <p class="mt-1">
                Based on `xenon/laravelbdsms` provider documentation and
                published `config/sms.php`.
            </p>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold">Credentials</h3>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addCustomCredential"
                >
                    Add Custom Field
                </Button>
            </div>

            <div
                v-if="form.credential_items.length === 0"
                class="rounded-lg border border-dashed p-4 text-sm text-muted-foreground"
            >
                No credential rows yet. Select a provider or add custom fields.
            </div>

            <div
                v-for="(item, index) in form.credential_items"
                :key="`${item.key}-${index}`"
                class="grid gap-3 rounded-lg border p-3 md:grid-cols-[minmax(180px,240px)_1fr_auto]"
            >
                <div class="space-y-2">
                    <Label :for="`credential-key-${index}`">Key</Label>
                    <Input
                        :id="`credential-key-${index}`"
                        v-model="item.key"
                        type="text"
                        :readonly="isSchemaCredentialKey(item.key)"
                        :class="
                            isSchemaCredentialKey(item.key) ? 'bg-muted' : ''
                        "
                        placeholder="api_key"
                    />
                    <p
                        v-if="selectedProviderFieldMap[item.key]?.required"
                        class="text-xs font-medium text-amber-700"
                    >
                        Required
                    </p>
                    <InputError
                        :message="form.errors[`credential_items.${index}.key`]"
                    />
                </div>

                <div class="space-y-2">
                    <Label :for="`credential-value-${index}`">
                        {{
                            selectedProviderFieldMap[item.key]?.label || 'Value'
                        }}
                    </Label>
                    <Input
                        :id="`credential-value-${index}`"
                        v-model="item.value"
                        :type="
                            selectedProviderFieldMap[item.key]?.sensitive
                                ? 'password'
                                : 'text'
                        "
                        :placeholder="
                            selectedProviderFieldMap[item.key]?.placeholder ||
                            'Enter value'
                        "
                        autocomplete="off"
                    />
                    <p class="text-xs text-muted-foreground">
                        {{
                            selectedProviderFieldMap[item.key]?.description ||
                            'Custom credential field.'
                        }}
                    </p>
                    <InputError
                        :message="
                            form.errors[`credential_items.${index}.value`]
                        "
                    />
                </div>

                <div class="flex items-end justify-end">
                    <Button
                        type="button"
                        variant="ghost"
                        class="text-rose-600 hover:text-rose-700"
                        :disabled="isSchemaCredentialKey(item.key)"
                        @click="removeCredential(index)"
                    >
                        Remove
                    </Button>
                </div>
            </div>

            <InputError :message="form.errors.credentials" />
            <InputError :message="form.errors.credentials_json" />
            <InputError :message="form.errors.credential_items" />
        </div>

        <div class="grid gap-3 rounded-xl border border-slate-200/80 p-4">
            <label class="flex items-center gap-2 text-sm">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 rounded border"
                />
                <span>Active</span>
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input
                    v-model="form.is_default"
                    type="checkbox"
                    class="h-4 w-4 rounded border"
                />
                <span>Set as default</span>
            </label>

            <InputError :message="form.errors.is_active" />
            <InputError :message="form.errors.is_default" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                {{ submitLabel }}
            </Button>
            <Link
                :href="cancelHref"
                class="inline-flex h-9 items-center rounded-md border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                Cancel
            </Link>
        </div>
    </form>
</template>
