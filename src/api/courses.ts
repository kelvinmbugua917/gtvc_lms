import { apiRequest } from "./client";
import { CourseOffering } from "./academic";

export async function getCourseOfferingByIdApi(offeringId: number): Promise<CourseOffering | null> {
  try {
    const res = await apiRequest<CourseOffering>(`/api/v1/course-offerings/${offeringId}`);
    return res.data;
  } catch (err) {
    console.warn("API fallback: Returning local offering data");
    return null;
  }
}
