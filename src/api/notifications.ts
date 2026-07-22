import { apiClient } from './client';

export interface NotificationItem {
  id: number;
  user_id: number;
  type: 'academic_material' | 'academic_assignment' | 'academic_quiz' | 'academic_grade' | 'attendance_warning' | 'announcement' | 'system';
  title: string;
  message: string;
  priority: 'normal' | 'important' | 'urgent';
  related_entity_type?: string | null;
  related_entity_id?: number | null;
  is_read: boolean | number;
  read_at?: string | null;
  created_at: string;
}

export interface NotificationListResponse {
  notifications: NotificationItem[];
  unread_count: number;
  limit: number;
  offset: number;
}

export const notificationsApi = {
  getNotifications: async (params?: { unread_only?: boolean; type?: string; limit?: number; offset?: number }): Promise<NotificationListResponse> => {
    const query = new URLSearchParams();
    if (params?.unread_only) query.append('unread_only', '1');
    if (params?.type) query.append('type', params.type);
    if (params?.limit) query.append('limit', String(params.limit));
    if (params?.offset) query.append('offset', String(params.offset));

    return apiClient.get<NotificationListResponse>(`/notifications?${query.toString()}`);
  },

  getUnreadCount: async (): Promise<{ unread_count: number }> => {
    return apiClient.get<{ unread_count: number }>('/notifications/unread-count');
  },

  getNotificationById: async (id: number): Promise<NotificationItem> => {
    return apiClient.get<NotificationItem>(`/notifications/${id}`);
  },

  markAsRead: async (id: number): Promise<{ id: number; is_read: number }> => {
    return apiClient.put<{ id: number; is_read: number }>(`/notifications/${id}/read`, {});
  },

  markAsUnread: async (id: number): Promise<{ id: number; is_read: number }> => {
    return apiClient.put<{ id: number; is_read: number }>(`/notifications/${id}/unread`, {});
  },

  markAllAsRead: async (): Promise<{ message: string }> => {
    return apiClient.post<{ message: string }>('/notifications/mark-all-read', {});
  },

  deleteNotification: async (id: number): Promise<{ id: number }> => {
    return apiClient.delete<{ id: number }>(`/notifications/${id}`);
  }
};
