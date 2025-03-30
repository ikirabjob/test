import api from './api';
import { User, UserLogin, UserRegistration } from '../types';

interface AuthResponse {
    token: string;
    user: User;
}

class AuthService {
    async login(credentials: UserLogin): Promise<AuthResponse> {
        const response = await api.post<AuthResponse>('/auth/login', credentials);
        this.setSession(response);
        return response;
    }

    async register(userData: UserRegistration): Promise<AuthResponse> {
        const response = await api.post<AuthResponse>('/auth/register', userData);
        this.setSession(response);
        return response;
    }

    async getCurrentUser(): Promise<User> {
        return api.get<User>('/auth/me');
    }

    logout(): void {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    }

    isAuthenticated(): boolean {
        return !!localStorage.getItem('token');
    }

    getToken(): string | null {
        return localStorage.getItem('token');
    }

    getUser(): User | null {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }

    isAdmin(): boolean {
        const user = this.getUser();
        return user ? user.role === 'admin' : false;
    }

    private setSession(authResult: AuthResponse): void {
        localStorage.setItem('token', authResult.token);
        localStorage.setItem('user', JSON.stringify(authResult.user));
    }
}

export default new AuthService();