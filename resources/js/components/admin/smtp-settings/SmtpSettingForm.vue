<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface DriverField {
    key: string;
    label: string;
    required: boolean;
    sensitive: boolean;
    placeholder?: string;
    description?: string;
}

interface Driver {
    name: string;
    label: string;
    fields: DriverField[];
    required_fields: string[];
}

const props = defineProps<{
    action: string;
    method?: string;
    submitLabel: string;
    cancelHref?: string;
    drivers: Driver[];
    initial?: {
        name?: string;
        driver?: string;
        from_address?: string;
        from_name?: string;
        is_default?: boolean;
        is_active?: boolean;
        credentials?: Record<string, string>;
    };
}>();

const toCredentialItems = (credentials: unknown) => {
    if (!credentials || typeof credentials !== 'object') {
        return [];
    }

    return Object.entries(credentials as Record<string, unknown>).map(
        ([key, value]) => ({
            key: String(key),
            value: value === null || value === undefined ? '' : String(value),
        }),
    );
};

const fallbackDriver = props.drivers[0]?.name ?? 'smtp';

const form = useForm({
    name: props.initial?.name ?? '',
    driver: props.initial?.driver ?? fallbackDriver,
    from_address: props.initial?.from_address ?? '',
    from_name: props.initial?.from_name ?? '',
    is_default: Boolean(props.initial?.is_default ?? false),
    is_active: Boolean(props.initial?.is_active ?? true),
    credential_items: toCredentialItems(props.initial?.credentials),
});

const selectedDriver = computed<Driver | null>(() => {
    return props.drivers.find((driver) => driver.name === form.driver) ?? null;
});

const selectedDriverFieldMap = computed(() => {
    return Object.fromEntries(
        (selectedDriver.value?.fields ?? []).map((field) => [field.key, field]),
    );
});

const selectedDriverFieldKeys = computed(() => {
    return (selectedDriver.value?.fields ?? []).map((field) => field.key);
});

const requiredKeysSummary = computed(() => {
    return selectedDriver.value?.required_fields?.join(', ') ?? '';
});

const synchronizeCredentialItems = () => {
    const schemaKeys = selectedDriverFieldKeys.value;
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
    () => form.driver,
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

const removeCredential = (index: number) => {
    form.credential_items.splice(index, 1);
};

const isSchemaCredentialKey = (key: string) => {
    return selectedDriverFieldKeys.value.includes(String(key));
};

const submit = () => {
    const payload = {
        name: form.name,
        driver: form.driver,
        from_address: form.from_address,
        from_name: form.from_name,
        is_default: form.is_default,
        is_active: form.is_active,
        credential_items: form.credential_items,
    };

    if (props.method?.toLowerCase() === 'put') {
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
                <Label for="name">Configuration Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Primary SMTP Gateway"
                    required
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="driver">Mail Driver</Label>
                <select
                    id="driver"
                    v-model="form.driver"
                    required
                    class="h-10 rounded-md border px-3 text-sm"
                >
                    <option value="" disabled>Select driver</option>
                    <option
                        v-for="driver in drivers"
                        :key="driver.name"
                        :value="driver.name"
                    >
                        {{ driver.label }}
                    </option>
                </select>
                <InputError :message="form.errors.driver" />
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <Label for="from_address">From Email Address</Label>
                <Input
                    id="from_address"
                    v-model="form.from_address"
                    type="email"
                    placeholder="noreply@example.com"
                />
                <p class="text-xs text-muted-foreground">
                    Default sender email address for outgoing mail.
                </p>
                <InputError :message="form.errors.from_address" />
            </div>

            <div class="grid gap-2">
                <Label for="from_name">From Name</Label>
                <Input
                    id="from_name"
                    v-model="form.from_name"
                    type="text"
                    placeholder="Your App Name"
                />
                <p class="text-xs text-muted-foreground">
                    Display name shown to email recipients.
                </p>
                <InputError :message="form.errors.from_name" />
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4 text-sm text-slate-600"
        >
            <p class="font-medium text-foreground">Credential Requirements</p>
            <p v-if="selectedDriver">
                Required: {{ requiredKeysSummary || 'None' }}.
            </p>
            <p v-else>Select a mail driver to view required credentials.</p>
            <p class="mt-1">
                Configuration fields are based on Laravel's mail driver
                requirements.
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
                No credential rows yet. Select a driver or add custom fields.
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
                        v-if="selectedDriverFieldMap[item.key]?.required"
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
                        {{ selectedDriverFieldMap[item.key]?.label || 'Value' }}
                    </Label>
                    <Input
                        :id="`credential-value-${index}`"
                        v-model="item.value"
                        :type="
                            selectedDriverFieldMap[item.key]?.sensitive
                                ? 'password'
                                : 'text'
                        "
                        :placeholder="
                            selectedDriverFieldMap[item.key]?.placeholder ||
                            'Enter value'
                        "
                        autocomplete="off"
                    />
                    <p class="text-xs text-muted-foreground">
                        {{
                            selectedDriverFieldMap[item.key]?.description ||
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

            <InputError
                :message="(form.errors as Record<string, string>).credentials"
            />
            <InputError
                :message="
                    (form.errors as Record<string, string>).credentials_json
                "
            />
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
