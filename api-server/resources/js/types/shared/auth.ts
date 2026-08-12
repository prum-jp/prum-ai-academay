export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: number;
}

export const ROLE_STUDENT = 0;
export const ROLE_MENTOR = 1;
