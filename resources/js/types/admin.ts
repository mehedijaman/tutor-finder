/**
 * Shared type definitions for admin pages and components.
 */

/**
 * Laravel pagination link structure.
 */
export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/**
 * Laravel paginated response wrapper.
 */
export type PaginatedResponse<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

/**
 * Common filter parameters used across admin index pages.
 */
export type BaseFilters = {
    q?: string;
    search?: string;
    status?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    trash?: boolean;
    page?: number;
};

/**
 * Table column definition for DataTable component.
 */
export type TableColumn = {
    key: string;
    label: string;
    sortable?: boolean;
    headerClass?: string;
    cellClass?: string;
};

/**
 * Base row type with common fields.
 */
export type BaseRow = {
    id: number;
    created_at: string;
    updated_at: string;
};

/**
 * Select option for dropdowns/filters.
 */
export type SelectOption = {
    value: string | number;
    label: string;
};

/**
 * Status counts for sidebar/filter badges.
 */
export type StatusCounts = Record<string, number>;

/**
 * Common props for admin index pages.
 */
export type AdminIndexPageProps<T, F extends BaseFilters = BaseFilters> = {
    items: PaginatedResponse<T>;
    filters: F;
    counts?: StatusCounts;
    pageTitle?: string;
};

// ============================================================
// Domain-specific row types for admin pages
// ============================================================

/**
 * Job row in admin jobs list.
 */
export type JobRow = BaseRow & {
    title: string;
    slug: string;
    guardian_name: string;
    category_name: string;
    class_name: string;
    city_name: string;
    applications_count: number;
    hiring_outcome: string | null;
    status: string;
    published_at: string | null;
    expires_at: string | null;
};

/**
 * Tutor row in admin tutors list.
 */
export type TutorRow = BaseRow & {
    name: string;
    email: string;
    phone: string | null;
    status: string;
    verification_status: string;
    profile_status: string | null;
    city_name: string | null;
    subjects_count: number;
};

/**
 * Guardian row in admin guardians list.
 */
export type GuardianRow = BaseRow & {
    name: string;
    email: string;
    phone: string | null;
    status: string;
    verification_status: string;
    jobs_count: number;
    city_name: string | null;
};

/**
 * User row in admin users list.
 */
export type UserRow = BaseRow & {
    name: string;
    email: string;
    role: string;
    status: string;
    email_verified_at: string | null;
};

/**
 * Blog post row in admin posts list.
 */
export type BlogPostRow = BaseRow & {
    title: string;
    slug: string;
    author_name: string;
    category_name: string | null;
    status: string;
    published_at: string | null;
    views_count: number;
};

/**
 * Verification request row in admin verifications list.
 */
export type VerificationRow = BaseRow & {
    user_name: string;
    user_email: string;
    role: string;
    status: string;
    fee_amount: number | null;
    currency: string | null;
    submitted_at: string | null;
};

/**
 * Invoice row in admin/tutor/guardian finance lists.
 */
export type InvoiceRow = BaseRow & {
    invoice_no: string;
    user_name: string;
    payer_name: string | null;
    type: string;
    status: string;
    amount: number;
    currency: string;
    due_date: string | null;
    paid_at: string | null;
};

/**
 * Payment row in admin finance payments list.
 */
export type PaymentRow = BaseRow & {
    invoice_no: string;
    gateway: string;
    provider_txn_id: string | null;
    amount: number;
    status: string;
};

/**
 * Refund request row in finance lists.
 */
export type RefundRow = BaseRow & {
    user_name: string;
    job_title: string | null;
    reason_text: string | null;
    status: string;
    amount: number;
    currency: string;
    requested_at: string;
    decided_at: string | null;
};

// ============================================================
// Taxonomy row types
// ============================================================

/**
 * Generic taxonomy row (categories, cities, areas, etc.)
 */
export type TaxonomyRow = BaseRow & {
    name: string;
    slug: string;
    status: string;
    sort_order?: number;
    parent_name?: string | null;
    children_count?: number;
};

/**
 * Country row.
 */
export type CountryRow = TaxonomyRow;

/**
 * City row with country reference.
 */
export type CityRow = TaxonomyRow & {
    country_name: string;
};

/**
 * Area row with city reference.
 */
export type AreaRow = TaxonomyRow & {
    city_name: string;
};

/**
 * Category row.
 */
export type CategoryRow = TaxonomyRow & {
    description?: string | null;
    classes_count?: number;
};

/**
 * School class row with category reference.
 */
export type SchoolClassRow = TaxonomyRow & {
    category_name: string;
    subjects_count?: number;
};

/**
 * Subject row with class reference.
 */
export type SubjectRow = TaxonomyRow & {
    class_name: string;
};
