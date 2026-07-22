import { apiClient } from './client';

export interface AdminUser {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
  status: 'active' | 'suspended' | 'inactive';
  created_at: string;
  last_login_at?: string;
  roles?: { id: number; name: string; description: string }[];
  departments?: { id: number; code: string; name: string; is_head_of_department: number }[];
}

export interface Role {
  id: number;
  name: string;
  description: string;
}

export interface Permission {
  id: number;
  name: string;
  module: string;
  description: string;
}

export interface SystemSettingItem {
  id: number;
  setting_key: string;
  setting_value: string;
  category: string;
  description: string;
  updated_at: string;
}

export interface AuditLogItem {
  id: number;
  user_id?: number;
  email?: string;
  first_name?: string;
  last_name?: string;
  action: string;
  ip_address?: string;
  user_agent?: string;
  details_json?: any;
  created_at: string;
}

export const adminApi = {
  getUsers: async (params?: { search?: string; role?: string; status?: string }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<AdminUser[]>(`/api/v1/admin/users${query ? `?${query}` : ''}`);
  },

  createUser: async (data: {
    email: string;
    first_name: string;
    last_name: string;
    password: string;
    role_id?: number;
    status?: string;
  }) => {
    return apiClient.post<{ message: string; id: number }>('/api/v1/admin/users', data);
  },

  updateUser: async (id: number, data: { first_name?: string; last_name?: string; status?: string; password?: string }) => {
    return apiClient.put<{ message: string; id: number }>(`/api/v1/admin/users/${id}`, data);
  },

  getRoles: async () => {
    return apiClient.get<{ roles: Role[]; permissions: Permission[] }>('/api/v1/admin/roles');
  },

  assignRoles: async (userId: number, roleIds: number[]) => {
    return apiClient.post<{ message: string; user_id: number }>('/api/v1/admin/roles/assign', {
      user_id: userId,
      role_ids: roleIds,
    });
  },

  getSettings: async () => {
    return apiClient.get<SystemSettingItem[]>('/api/v1/admin/settings');
  },

  updateSettings: async (settings: Record<string, string>) => {
    return apiClient.put<{ message: string }>('/api/v1/admin/settings', { settings });
  },

  getAuditLogs: async (params?: { search?: string; action?: string }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiClient.get<AuditLogItem[]>(`/api/v1/admin/audit-logs${query ? `?${query}` : ''}`);
  },
};
