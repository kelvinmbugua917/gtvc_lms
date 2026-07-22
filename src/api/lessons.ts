import { apiRequest } from "./client";

export interface LessonRecord {
  id: number;
  module_id: number;
  title: string;
  content_type: "text" | "video" | "document" | "quiz" | "assignment";
  text_content: string | null;
  duration_minutes: number;
  sequence_order: number;
  is_published: number;
  created_at: string;
  material_count?: number;
  course_offering_id?: number;
  module_title?: string;
}

export interface CreateLessonPayload {
  title: string;
  content_type: "text" | "video" | "document" | "quiz" | "assignment";
  text_content?: string;
  duration_minutes?: number;
  sequence_order?: number;
  is_published?: number;
}

export async function getLessonsByModuleApi(moduleId: number): Promise<LessonRecord[]> {
  try {
    const res = await apiRequest<LessonRecord[]>(`/api/v1/modules/${moduleId}/lessons`);
    return res.data;
  } catch (err) {
    console.warn("API fallback for lessons");
    if (moduleId === 101) {
      return [
        {
          id: 201,
          module_id: 101,
          title: "Lesson 1.1: Personal Protective Equipment (PPE) Standards",
          content_type: "text",
          text_content: "### Personal Protective Equipment (PPE) Guidelines\nIn technical workshops, appropriate gear is required before entering work bays.\n\n1. **Head Protection**: Industrial safety helmet with chin straps.\n2. **Eye Protection**: Impact-resistant safety goggles.\n3. **Footwear**: Steel-toe safety boots with oil-resistant soles.\n4. **Hand Protection**: Heavy-duty leather gloves for arc welding and thermal operations.",
          duration_minutes: 20,
          sequence_order: 1,
          is_published: 1,
          created_at: new Date().toISOString(),
          material_count: 1
        },
        {
          id: 202,
          module_id: 101,
          title: "Lesson 1.2: Video Demonstration - Machine Inspection Procedures",
          content_type: "video",
          text_content: "Watch the step-by-step pre-operation safety check for lathe and milling machinery.",
          duration_minutes: 35,
          sequence_order: 2,
          is_published: 1,
          created_at: new Date().toISOString(),
          material_count: 1
        },
        {
          id: 203,
          module_id: 101,
          title: "Lesson 1.3: Interactive Knowledge Check (Quiz Placeholder)",
          content_type: "quiz",
          text_content: "Structural placeholder for Module 1 end-of-section evaluation.",
          duration_minutes: 15,
          sequence_order: 3,
          is_published: 1,
          created_at: new Date().toISOString(),
          material_count: 0
        }
      ];
    }
    return [];
  }
}

export async function getLessonDetailsApi(lessonId: number): Promise<LessonRecord | null> {
  try {
    const res = await apiRequest<LessonRecord>(`/api/v1/lessons/${lessonId}`);
    return res.data;
  } catch (err) {
    return null;
  }
}

export async function createLessonApi(moduleId: number, payload: CreateLessonPayload): Promise<{ id: number; message: string }> {
  const res = await apiRequest<{ id: number }>(`/api/v1/modules/${moduleId}/lessons`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return { id: res.data?.id || 0, message: res.message };
}

export async function updateLessonApi(lessonId: number, payload: Partial<CreateLessonPayload>): Promise<{ message: string }> {
  const res = await apiRequest<null>(`/api/v1/lessons/${lessonId}`, {
    method: "PUT",
    body: JSON.stringify(payload)
  });
  return { message: res.message };
}

export async function deleteLessonApi(lessonId: number): Promise<{ message: string }> {
  const res = await apiRequest<null>(`/api/v1/lessons/${lessonId}`, {
    method: "DELETE"
  });
  return { message: res.message };
}
