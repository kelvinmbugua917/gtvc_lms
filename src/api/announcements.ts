import { apiClient } from './client';

export interface AnnouncementItem {
  id: number;
  title: string;
  content: string;
  priority: 'normal' | 'important' | 'urgent';
  target_role: string;
  target_department_id?: number | null;
  target_program_id?: number | null;
  target_class_id?: number | null;
  target_course_offering_id?: number | null;
  author_user_id: number;
  attachment_path?: string | null;
  attachment_name?: string | null;
  is_published: boolean | number;
  is_archived: boolean | number;
  publish_at?: string | null;
  expire_at?: string | null;
  created_at: string;
  updated_at: string;
  author_first_name?: string;
  author_last_name?: string;
  author_email?: string;
  department_name?: string;
  department_code?: string;
  program_name?: string;
  class_name?: string;
}

export const announcementsApi = {
  getAnnouncements: async (params?: { search?: string; priority?: string; management_view?: boolean }): Promise<AnnouncementItem[]> => {
    const query = new URLSearchParams();
    if (params?.search) query.append('search', params.search);
    if (params?.priority) query.append('priority', params.priority);
    if (params?.management_view) query.append('management_view', '1');

    const res = await apiClient.get<AnnouncementItem[]>(`/announcements?${query.toString()}`);
    return res;
  },

  getAnnouncementById: async (id: number): Promise<AnnouncementItem> => {
    return apiClient.get<AnnouncementItem>(`/announcements/${id}`);
  },

  createAnnouncement: async (data: FormData | Record<string, any>): Promise<{ id: number; message: string }> => {
    if (data instanceof FormData) {
      return apiClient.post<{ id: number; message: string }>('/announcements', data);
    }
    return apiClient.post<{ id: number; message: string }>('/announcements', data);
  },

  updateAnnouncement: async (id: number, data: FormData | Record<string, any>): Promise<{ id: number }> => {
    return apiClient.put<{ id: number }>(`/announcements/${id}`, data);
  },

  publishAnnouncement: async (id: number): Promise<{ id: number }> => {
    return apiClient.post<{ id: number }>(`/announcements/${id}/publish`, {});
  },

  archiveAnnouncement: async (id: number): Promise<{ id: number }> => {
    return apiClient.post<{ id: number }>(`/announcements/${id}/archive`, {});
  },

  deleteAnnouncement: async (id: number): Promise<{ id: number }> => {
    return apiClient.delete<{ id: number }>(`/announcements/${id}`);
  },

  getDownloadUrl: (id: number): string => {
    return `/api/v1/announcements/${id}/download`;
  }
};
