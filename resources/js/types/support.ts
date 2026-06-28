export interface TicketUser {
    id: number;
    name: string;
    role: string;
    avatar?: string | null;
    email?: string;
}

export interface TicketAttachment {
    id: number;
    url: string;
    name: string;
    size: number;
}

export interface TicketMessage {
    id: number;
    user_id: number;
    body: string;
    user: TicketUser;
    is_admin_reply: boolean;
    attachments: TicketAttachment[];
    created_at: string;
}

export interface TicketRow {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    user_name?: string | null;
    assigned_admin_name?: string | null;
    last_reply_at: string | null;
    created_at: string;
}

export interface TicketDetail {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    assigned_to: number | null;
    created_at: string;
    closed_at: string | null;
    user: TicketUser;
    assigned_admin?: { id: number; name: string } | null;
    closed_by?: { id: number; name: string } | null;
    messages: TicketMessage[];
}
