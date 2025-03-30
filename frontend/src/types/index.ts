export interface User {
    id: number;
    email: string;
    firstName: string;
    lastName: string;
    role: 'user' | 'admin';
}

export interface Event {
    id: number;
    title: string;
    description: string | null;
    location: string | null;
    start_date: string;
    end_date: string;
    capacity: number | null;
    is_public: boolean;
    creator_id: number;
    created_at: string;
    updated_at: string;
    registration_count?: number;
}

export interface Category {
    id: number;
    name: string;
    description: string | null;
}

export interface EventRegistration {
    id: number;
    event_id: number;
    user_id: number;
    registration_date: string;
    status: 'registered' | 'cancelled' | 'attended';
}

export interface Attendee {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    registration_date: string;
    status: 'registered' | 'cancelled' | 'attended';
}

export interface ApiResponse<T> {
    data: T;
}

export interface ErrorResponse {
    error: string;
}

export interface AuthState {
    user: User | null;
    token: string | null;
    isAuthenticated: boolean;
    isLoading: boolean;
    error: string | null;
}

export interface UserRegistration {
    email: string;
    password: string;
    firstName: string;
    lastName: string;
}

export interface UserLogin {
    email: string;
    password: string;
}

export interface EventFormData {
    title: string;
    description: string;
    location: string;
    start_date: string;
    end_date: string;
    capacity: number | null;
    is_public: boolean;
    categories: number[];
}