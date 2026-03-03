<script setup lang="ts">
import Highlight from '@tiptap/extension-highlight';
import HorizontalRule from '@tiptap/extension-horizontal-rule';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import Table from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { Editor, EditorContent } from '@tiptap/vue-3';
import {
    AlignCenter,
    AlignJustify,
    AlignLeft,
    AlignRight,
    Bold,
    Code2,
    CornerDownLeft,
    Eraser,
    Heading,
    Heading1,
    Heading2,
    Heading3,
    Heading4,
    Highlighter,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    ListTodo,
    Loader2,
    Minus,
    Pilcrow,
    Plus,
    Quote,
    Redo2,
    SquareCode,
    Strikethrough,
    Subscript as SubscriptIcon,
    Superscript as SuperscriptIcon,
    Table2,
    Trash2,
    Underline as UnderlineIcon,
    Undo2,
    Unlink,
} from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

type OptionalExtensionLoaderResult = {
    extension: unknown | null;
    loaded: boolean;
};

type ConfigurableExtension = {
    configure: (config: Record<string, unknown>) => unknown;
};

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        uploadUrl?: string;
        maxImageSizeMb?: number;
    }>(),
    {
        modelValue: '',
        placeholder: 'Write content...',
        uploadUrl: '/admin/blog/uploads/images',
        maxImageSizeMb: 5,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const editor = ref<Editor | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const linkDialogOpen = ref(false);
const linkUrl = ref('');
const uploadError = ref<string | null>(null);
const pendingUploadCount = ref(0);
const taskListEnabled = ref(false);
const characterCountEnabled = ref(false);

const isUploadingImage = computed(() => pendingUploadCount.value > 0);

const characterCount = computed<number | null>(() => {
    if (!editor.value || !characterCountEnabled.value) {
        return null;
    }

    const storage = editor.value.storage as {
        characterCount?: { characters?: () => number };
    };

    if (typeof storage.characterCount?.characters !== 'function') {
        return null;
    }

    return storage.characterCount.characters();
});

const buttonActiveClass = 'bg-accent text-accent-foreground';

onMounted(() => {
    void initializeEditor();
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) {
            return;
        }

        const currentHtml = editor.value.getHTML();

        if ((value ?? '') !== currentHtml) {
            editor.value.commands.setContent(value ?? '<p></p>', false);
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function runCommand(command: (currentEditor: Editor) => void): void {
    if (!editor.value) {
        return;
    }

    command(editor.value);
}

function canRun(command: (currentEditor: Editor) => boolean): boolean {
    if (!editor.value) {
        return false;
    }

    return command(editor.value);
}

function isActive(
    nameOrAttributes: string | Record<string, unknown>,
    attributes: Record<string, unknown> = {},
): boolean {
    if (!editor.value) {
        return false;
    }

    if (typeof nameOrAttributes === 'string') {
        return editor.value.isActive(nameOrAttributes, attributes);
    }

    return editor.value.isActive(nameOrAttributes);
}

function openLinkDialog(): void {
    if (!editor.value) {
        return;
    }

    linkUrl.value = (editor.value.getAttributes('link').href ?? '') as string;
    linkDialogOpen.value = true;
}

function saveLink(): void {
    if (!editor.value) {
        linkDialogOpen.value = false;

        return;
    }

    const normalized = linkUrl.value.trim();

    if (normalized === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        linkDialogOpen.value = false;

        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({
            href: normalized,
            target: '_blank',
            rel: 'noopener noreferrer nofollow',
        })
        .run();

    linkDialogOpen.value = false;
}

function removeLink(): void {
    runCommand((currentEditor) => {
        currentEditor.chain().focus().extendMarkRange('link').unsetLink().run();
    });
}

function triggerImagePicker(): void {
    fileInput.value?.click();
}

async function handleImageInput(event: Event): Promise<void> {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files ?? []);
    target.value = '';

    await uploadAndInsertImages(files);
}

async function uploadAndInsertImages(
    files: File[],
    dropPosition: number | null = null,
): Promise<void> {
    if (!editor.value || files.length === 0) {
        return;
    }

    let currentDropPosition = dropPosition;

    for (const file of files) {
        await uploadSingleImage(file, currentDropPosition);
        currentDropPosition = null;
    }
}

async function uploadSingleImage(
    file: File,
    dropPosition: number | null,
): Promise<void> {
    if (!editor.value) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        uploadError.value = 'Only image files are allowed.';

        return;
    }

    const maxBytes = Math.max(1, props.maxImageSizeMb) * 1024 * 1024;

    if (file.size > maxBytes) {
        uploadError.value = `Image must be ${props.maxImageSizeMb} MB or smaller.`;

        return;
    }

    pendingUploadCount.value++;
    uploadError.value = null;

    try {
        const formData = new FormData();
        formData.append('image', file);
        const csrfToken = getCsrfToken();

        if (csrfToken !== null) {
            formData.append('_token', csrfToken);
        }

        const response = await fetch(props.uploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken !== null ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: formData,
        });

        const payload = (await response.json().catch(() => null)) as {
            url?: string;
            message?: string;
        } | null;

        if (!response.ok) {
            throw new Error(payload?.message ?? 'Image upload failed.');
        }

        const imageUrl = payload?.url;

        if (typeof imageUrl !== 'string' || imageUrl.trim() === '') {
            throw new Error('Upload succeeded but no image URL was returned.');
        }

        const chain = editor.value.chain().focus();

        if (typeof dropPosition === 'number') {
            chain.setTextSelection(dropPosition);
        }

        chain
            .setImage({
                src: imageUrl,
                alt: file.name,
                title: file.name,
            })
            .run();
    } catch (error) {
        uploadError.value =
            error instanceof Error ? error.message : 'Image upload failed.';
    } finally {
        pendingUploadCount.value--;
    }
}

function getCsrfToken(): string | null {
    const token = document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (typeof token !== 'string') {
        return null;
    }

    const trimmedToken = token.trim();

    return trimmedToken === '' ? null : trimmedToken;
}

async function initializeEditor(): Promise<void> {
    const extensions = await buildExtensions();

    editor.value = new Editor({
        content: props.modelValue || '<p></p>',
        extensions,
        editorProps: {
            attributes: {
                class: [
                    'min-h-[320px] w-full px-4 py-3 text-sm leading-relaxed outline-none',
                    '[&_p]:my-3 [&_ul]:my-3 [&_ol]:my-3 [&_blockquote]:my-4 [&_blockquote]:border-l-2 [&_blockquote]:pl-3',
                    '[&_pre]:my-4 [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:bg-muted [&_pre]:p-3',
                    '[&_table]:my-4 [&_table]:w-full [&_table]:border-collapse [&_th]:border [&_th]:bg-muted [&_th]:p-2 [&_td]:border [&_td]:p-2',
                    '[&_a]:text-primary [&_a]:underline [&_img]:my-3 [&_img]:max-h-[420px] [&_img]:rounded-md',
                ].join(' '),
            },
            handleDrop: (view, event, _slice, moved) => {
                if (moved) {
                    return false;
                }

                const files = Array.from(
                    event.dataTransfer?.files ?? [],
                ).filter((file) => file.type.startsWith('image/'));

                if (files.length === 0) {
                    return false;
                }

                event.preventDefault();

                const resolvedPosition = view.posAtCoords({
                    left: event.clientX,
                    top: event.clientY,
                });

                void uploadAndInsertImages(
                    files,
                    resolvedPosition?.pos ?? null,
                );

                return true;
            },
            handlePaste: (_view, event) => {
                const files = Array.from(
                    event.clipboardData?.files ?? [],
                ).filter((file) => file.type.startsWith('image/'));

                if (files.length === 0) {
                    return false;
                }

                event.preventDefault();
                void uploadAndInsertImages(files);

                return true;
            },
        },
        onUpdate: ({ editor: currentEditor }) => {
            emit('update:modelValue', currentEditor.getHTML());
        },
    });

    await nextTick();
}

async function buildExtensions(): Promise<unknown[]> {
    const extensions: unknown[] = [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4],
            },
        }),
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Highlight,
        Subscript,
        Superscript,
        Image,
        Table.configure({
            resizable: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        HorizontalRule,
    ];

    const placeholderExtension = await loadOptionalExtension(
        '@tiptap/extension-placeholder',
    );

    if (
        placeholderExtension.loaded &&
        placeholderExtension.extension !== null
    ) {
        extensions.push(
            (placeholderExtension.extension as ConfigurableExtension).configure(
                {
                    placeholder: props.placeholder,
                },
            ),
        );
    }

    const taskListExtension = await loadOptionalExtension(
        '@tiptap/extension-task-list',
    );
    const taskItemExtension = await loadOptionalExtension(
        '@tiptap/extension-task-item',
    );

    if (
        taskListExtension.loaded &&
        taskItemExtension.loaded &&
        taskListExtension.extension !== null &&
        taskItemExtension.extension !== null
    ) {
        taskListEnabled.value = true;
        extensions.push(taskListExtension.extension);
        extensions.push(
            (taskItemExtension.extension as ConfigurableExtension).configure({
                nested: true,
            }),
        );
    }

    const typographyExtension = await loadOptionalExtension(
        '@tiptap/extension-typography',
    );

    if (typographyExtension.loaded && typographyExtension.extension !== null) {
        extensions.push(typographyExtension.extension);
    }

    const characterCountExtension = await loadOptionalExtension(
        '@tiptap/extension-character-count',
    );

    if (
        characterCountExtension.loaded &&
        characterCountExtension.extension !== null
    ) {
        characterCountEnabled.value = true;
        extensions.push(characterCountExtension.extension);
    }

    return extensions;
}

async function loadOptionalExtension(
    moduleName: string,
): Promise<OptionalExtensionLoaderResult> {
    try {
        const module = await import(/* @vite-ignore */ moduleName);

        return {
            extension: module.default ?? null,
            loaded: module.default !== undefined,
        };
    } catch {
        return {
            extension: null,
            loaded: false,
        };
    }
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border bg-white">
        <TooltipProvider :delay-duration="100">
            <div
                class="sticky top-0 z-20 flex flex-wrap items-center gap-1 border-b bg-white/95 p-2 backdrop-blur supports-[backdrop-filter]:bg-white/80"
            >
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('bold') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleBold()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleBold()
                                        .run(),
                                )
                            "
                        >
                            <Bold class="size-4" />
                            <span class="sr-only">Bold</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Bold</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('italic') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleItalic()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleItalic()
                                        .run(),
                                )
                            "
                        >
                            <Italic class="size-4" />
                            <span class="sr-only">Italic</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Italic</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('underline'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleUnderline()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleUnderline()
                                        .run(),
                                )
                            "
                        >
                            <UnderlineIcon class="size-4" />
                            <span class="sr-only">Underline</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Underline</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('strike') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleStrike()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleStrike()
                                        .run(),
                                )
                            "
                        >
                            <Strikethrough class="size-4" />
                            <span class="sr-only">Strike</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Strikethrough</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('code') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleCode()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleCode()
                                        .run(),
                                )
                            "
                        >
                            <Code2 class="size-4" />
                            <span class="sr-only">Inline Code</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Inline Code</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('highlight'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleHighlight()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleHighlight()
                                        .run(),
                                )
                            "
                        >
                            <Highlighter class="size-4" />
                            <span class="sr-only">Highlight</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Highlight</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('subscript'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleSubscript()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleSubscript()
                                        .run(),
                                )
                            "
                        >
                            <SubscriptIcon class="size-4" />
                            <span class="sr-only">Subscript</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Subscript</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('superscript'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleSuperscript()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleSuperscript()
                                        .run(),
                                )
                            "
                        >
                            <SuperscriptIcon class="size-4" />
                            <span class="sr-only">Superscript</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Superscript</TooltipContent>
                </Tooltip>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('paragraph'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setParagraph()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setParagraph()
                                        .run(),
                                )
                            "
                        >
                            <Pilcrow class="size-4" />
                            <span class="sr-only">Paragraph</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Paragraph</TooltipContent>
                </Tooltip>

                <DropdownMenu>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    type="button"
                                    size="icon-sm"
                                    variant="outline"
                                    :class="{
                                        [buttonActiveClass]:
                                            isActive('heading', { level: 1 }) ||
                                            isActive('heading', { level: 2 }) ||
                                            isActive('heading', { level: 3 }) ||
                                            isActive('heading', { level: 4 }),
                                    }"
                                >
                                    <Heading class="size-4" />
                                    <span class="sr-only">Headings</span>
                                </Button>
                            </DropdownMenuTrigger>
                        </TooltipTrigger>
                        <TooltipContent>Headings</TooltipContent>
                    </Tooltip>
                    <DropdownMenuContent align="start" class="w-40">
                        <DropdownMenuItem
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHeading({ level: 1 })
                                        .run(),
                                )
                            "
                        >
                            <Heading1 class="mr-2 size-4" />
                            H1
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHeading({ level: 2 })
                                        .run(),
                                )
                            "
                        >
                            <Heading2 class="mr-2 size-4" />
                            H2
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHeading({ level: 3 })
                                        .run(),
                                )
                            "
                        >
                            <Heading3 class="mr-2 size-4" />
                            H3
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHeading({ level: 4 })
                                        .run(),
                                )
                            "
                        >
                            <Heading4 class="mr-2 size-4" />
                            H4
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('blockquote'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleBlockquote()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleBlockquote()
                                        .run(),
                                )
                            "
                        >
                            <Quote class="size-4" />
                            <span class="sr-only">Blockquote</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Blockquote</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('codeBlock'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleCodeBlock()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleCodeBlock()
                                        .run(),
                                )
                            "
                        >
                            <SquareCode class="size-4" />
                            <span class="sr-only">Code Block</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Code Block</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setHardBreak()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHardBreak()
                                        .run(),
                                )
                            "
                        >
                            <CornerDownLeft class="size-4" />
                            <span class="sr-only">Hard Break</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Hard Break</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setHorizontalRule()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setHorizontalRule()
                                        .run(),
                                )
                            "
                        >
                            <Minus class="size-4" />
                            <span class="sr-only">Horizontal Rule</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Horizontal Rule</TooltipContent>
                </Tooltip>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('bulletList'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleBulletList()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleBulletList()
                                        .run(),
                                )
                            "
                        >
                            <List class="size-4" />
                            <span class="sr-only">Bullet List</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Bullet List</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('orderedList'),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleOrderedList()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleOrderedList()
                                        .run(),
                                )
                            "
                        >
                            <ListOrdered class="size-4" />
                            <span class="sr-only">Ordered List</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Ordered List</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive('taskList'),
                            }"
                            :disabled="
                                !taskListEnabled ||
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleTaskList()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleTaskList()
                                        .run(),
                                )
                            "
                        >
                            <ListTodo class="size-4" />
                            <span class="sr-only">Task List</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        {{
                            taskListEnabled
                                ? 'Task List'
                                : 'Task List (extension unavailable)'
                        }}
                    </TooltipContent>
                </Tooltip>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive({
                                    textAlign: 'left',
                                }),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setTextAlign('left')
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setTextAlign('left')
                                        .run(),
                                )
                            "
                        >
                            <AlignLeft class="size-4" />
                            <span class="sr-only">Align Left</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Align Left</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive({
                                    textAlign: 'center',
                                }),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setTextAlign('center')
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setTextAlign('center')
                                        .run(),
                                )
                            "
                        >
                            <AlignCenter class="size-4" />
                            <span class="sr-only">Align Center</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Align Center</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive({
                                    textAlign: 'right',
                                }),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setTextAlign('right')
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setTextAlign('right')
                                        .run(),
                                )
                            "
                        >
                            <AlignRight class="size-4" />
                            <span class="sr-only">Align Right</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Align Right</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{
                                [buttonActiveClass]: isActive({
                                    textAlign: 'justify',
                                }),
                            }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .setTextAlign('justify')
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .setTextAlign('justify')
                                        .run(),
                                )
                            "
                        >
                            <AlignJustify class="size-4" />
                            <span class="sr-only">Justify</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Justify</TooltipContent>
                </Tooltip>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('link') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .extendMarkRange('link')
                                        .run(),
                                )
                            "
                            @click="openLinkDialog"
                        >
                            <Link2 class="size-4" />
                            <span class="sr-only">Add or Edit Link</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Add or Edit Link</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .unsetLink()
                                        .run(),
                                )
                            "
                            @click="removeLink"
                        >
                            <Unlink class="size-4" />
                            <span class="sr-only">Remove Link</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Remove Link</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="isUploadingImage"
                            @click="triggerImagePicker"
                        >
                            <Loader2
                                v-if="isUploadingImage"
                                class="size-4 animate-spin"
                            />
                            <ImagePlus v-else class="size-4" />
                            <span class="sr-only">Insert Image</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Insert Image</TooltipContent>
                </Tooltip>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :class="{ [buttonActiveClass]: isActive('table') }"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .insertTable({
                                            rows: 3,
                                            cols: 3,
                                            withHeaderRow: true,
                                        })
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .insertTable({
                                            rows: 3,
                                            cols: 3,
                                            withHeaderRow: true,
                                        })
                                        .run(),
                                )
                            "
                        >
                            <Table2 class="size-4" />
                            <span class="sr-only">Insert Table</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Insert 3x3 Table</TooltipContent>
                </Tooltip>

                <DropdownMenu>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    type="button"
                                    size="icon-sm"
                                    variant="outline"
                                    :disabled="!isActive('table')"
                                >
                                    <Plus class="size-4" />
                                    <span class="sr-only">Table Actions</span>
                                </Button>
                            </DropdownMenuTrigger>
                        </TooltipTrigger>
                        <TooltipContent>Table Actions</TooltipContent>
                    </Tooltip>

                    <DropdownMenuContent align="start" class="w-56">
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .addRowBefore()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .addRowBefore()
                                        .run(),
                                )
                            "
                        >
                            Add Row Above
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .addRowAfter()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .addRowAfter()
                                        .run(),
                                )
                            "
                        >
                            Add Row Below
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .addColumnBefore()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .addColumnBefore()
                                        .run(),
                                )
                            "
                        >
                            Add Column Left
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .addColumnAfter()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .addColumnAfter()
                                        .run(),
                                )
                            "
                        >
                            Add Column Right
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .deleteRow()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .deleteRow()
                                        .run(),
                                )
                            "
                        >
                            Delete Row
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .deleteColumn()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .deleteColumn()
                                        .run(),
                                )
                            "
                        >
                            Delete Column
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .toggleHeaderRow()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .toggleHeaderRow()
                                        .run(),
                                )
                            "
                        >
                            Toggle Header Row
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .deleteTable()
                                        .run(),
                                )
                            "
                            @select.prevent="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .deleteTable()
                                        .run(),
                                )
                            "
                            class="text-destructive"
                        >
                            <Trash2 class="mr-2 size-4" />
                            Delete Table
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Separator orientation="vertical" class="mx-1 h-7" />

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .unsetAllMarks()
                                        .clearNodes()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor
                                        .chain()
                                        .focus()
                                        .unsetAllMarks()
                                        .clearNodes()
                                        .run(),
                                )
                            "
                        >
                            <Eraser class="size-4" />
                            <span class="sr-only">Clear Formatting</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Clear Formatting</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .undo()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor.chain().focus().undo().run(),
                                )
                            "
                        >
                            <Undo2 class="size-4" />
                            <span class="sr-only">Undo</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Undo</TooltipContent>
                </Tooltip>

                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="outline"
                            :disabled="
                                !canRun((currentEditor) =>
                                    currentEditor
                                        .can()
                                        .chain()
                                        .focus()
                                        .redo()
                                        .run(),
                                )
                            "
                            @click="
                                runCommand((currentEditor) =>
                                    currentEditor.chain().focus().redo().run(),
                                )
                            "
                        >
                            <Redo2 class="size-4" />
                            <span class="sr-only">Redo</span>
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Redo</TooltipContent>
                </Tooltip>

                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleImageInput"
                />
            </div>
        </TooltipProvider>

        <EditorContent :editor="editor" />

        <div
            class="flex flex-wrap items-center justify-between gap-2 border-t bg-muted/20 px-3 py-2 text-xs text-muted-foreground"
        >
            <p>Tip: paste or drag images directly into the editor to upload.</p>
            <p v-if="characterCount !== null">
                Characters: {{ characterCount }}
            </p>
        </div>

        <div
            v-if="uploadError"
            class="border-t border-destructive/20 bg-destructive/5 px-3 py-2 text-xs text-destructive"
        >
            {{ uploadError }}
        </div>
    </div>

    <Dialog :open="linkDialogOpen" @update:open="linkDialogOpen = $event">
        <DialogContent>
            <DialogHeader class="space-y-2">
                <DialogTitle>Insert Link</DialogTitle>
                <DialogDescription>
                    Enter a valid URL. Leave blank to remove the link.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2">
                <Label for="tiptap-link-url">URL</Label>
                <Input
                    id="tiptap-link-url"
                    v-model="linkUrl"
                    type="url"
                    placeholder="https://example.com"
                    autocomplete="off"
                    @keydown.enter.prevent="saveLink"
                />
            </div>

            <DialogFooter class="gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="linkDialogOpen = false"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="
                        removeLink();
                        linkDialogOpen = false;
                    "
                >
                    Remove Link
                </Button>
                <Button type="button" @click="saveLink"> Save Link </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
