import React, { useState, useEffect } from "react";
import { 
  BookOpen, Video, FileText, CheckCircle2, Circle, ChevronRight, ChevronDown, 
  Download, ExternalLink, Plus, Edit3, Trash2, Eye, EyeOff, PlayCircle, 
  BarChart2, RefreshCw, AlertCircle, ArrowLeft, ArrowRight, Layers, HelpCircle, FileCheck
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { useLms } from "../context/LmsContext";
import { getCourseOfferingsApi, CourseOffering } from "../api/academic";
import { getModulesByOfferingApi, createModuleApi, updateModuleApi, deleteModuleApi, CourseModuleRecord } from "../api/modules";
import { getLessonsByModuleApi, createLessonApi, updateLessonApi, deleteLessonApi, LessonRecord } from "../api/lessons";
import { getMaterialsByLessonApi, createMaterialApi, deleteMaterialApi, getMaterialDownloadUrl, LearningMaterialRecord } from "../api/materials";
import { getCourseProgressSummaryApi, getLessonProgressApi, saveLessonProgressApi, getCourseProgressOverviewApi, CourseProgressSummary, StudentProgressOverviewItem } from "../api/progress";

export default function LearningContentPage() {
  const { authUser } = useLms();

  const isStudent = authUser?.roles?.some(r => r.name === "student") ?? false;
  const isLecturer = authUser?.roles?.some(r => r.name === "lecturer" || r.name === "super_admin" || r.name === "admin") ?? true;

  // View state
  const [mode, setMode] = useState<"learn" | "manage" | "analytics">(isStudent ? "learn" : "manage");

  // Selection states
  const [offerings, setOfferings] = useState<CourseOffering[]>([]);
  const [selectedOfferingId, setSelectedOfferingId] = useState<number>(0);
  const [modules, setModules] = useState<CourseModuleRecord[]>([]);
  const [expandedModuleIds, setExpandedModuleIds] = useState<number[]>([]);
  const [selectedModuleId, setSelectedModuleId] = useState<number>(0);

  // Lessons and Materials state
  const [lessonsMap, setLessonsMap] = useState<Record<number, LessonRecord[]>>({});
  const [selectedLesson, setSelectedLesson] = useState<LessonRecord | null>(null);
  const [materials, setMaterials] = useState<LearningMaterialRecord[]>([]);

  // Progress state
  const [courseProgress, setCourseProgress] = useState<CourseProgressSummary | null>(null);
  const [isLessonCompleted, setIsLessonCompleted] = useState<boolean>(false);
  const [progressOverview, setProgressOverview] = useState<StudentProgressOverviewItem[]>([]);

  // UI state
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  // Modals state
  const [showModuleModal, setShowModuleModal] = useState<boolean>(false);
  const [moduleForm, setModuleForm] = useState({ title: "", description: "" });

  const [showLessonModal, setShowLessonModal] = useState<boolean>(false);
  const [lessonForm, setLessonForm] = useState({
    module_id: 0,
    title: "",
    content_type: "text" as "text" | "video" | "document" | "quiz" | "assignment",
    text_content: "",
    duration_minutes: 30,
    is_published: 1
  });

  const [showMaterialModal, setShowMaterialModal] = useState<boolean>(false);
  const [materialForm, setMaterialForm] = useState({
    lesson_id: 0,
    title: "",
    external_url: ""
  });
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  // Fetch initial Course Offerings
  const loadOfferings = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await getCourseOfferingsApi();
      setOfferings(data);
      if (data.length > 0) {
        setSelectedOfferingId(data[0].id);
      }
    } catch (err: any) {
      setError(err?.message || "Failed to load assigned course offerings");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOfferings();
  }, []);

  // Fetch Modules & Course Progress when offering changes
  useEffect(() => {
    if (!selectedOfferingId) return;

    const fetchOfferingContent = async () => {
      setLoading(true);
      setError(null);
      try {
        const [mods, prog, overview] = await Promise.all([
          getModulesByOfferingApi(selectedOfferingId),
          getCourseProgressSummaryApi(selectedOfferingId).catch(() => null),
          getCourseProgressOverviewApi(selectedOfferingId).catch(() => [])
        ]);

        setModules(mods);
        setCourseProgress(prog);
        setProgressOverview(overview);

        if (mods.length > 0) {
          setExpandedModuleIds([mods[0].id]);
          setSelectedModuleId(mods[0].id);
        } else {
          setLessonsMap({});
          setSelectedLesson(null);
        }
      } catch (err: any) {
        setError(err?.message || "Failed to load course modules");
      } finally {
        setLoading(false);
      }
    };

    fetchOfferingContent();
  }, [selectedOfferingId]);

  // Fetch Lessons for expanded modules
  useEffect(() => {
    if (expandedModuleIds.length === 0) return;

    expandedModuleIds.forEach(async (modId) => {
      if (!lessonsMap[modId]) {
        try {
          const lns = await getLessonsByModuleApi(modId);
          setLessonsMap(prev => ({ ...prev, [modId]: lns }));
          
          if (!selectedLesson && lns.length > 0) {
            setSelectedLesson(lns[0]);
          }
        } catch (err) {
          console.warn("Failed to fetch lessons for module", modId);
        }
      }
    });
  }, [expandedModuleIds]);

  // Fetch Materials & Lesson Progress when selected lesson changes
  useEffect(() => {
    if (!selectedLesson) {
      setMaterials([]);
      setIsLessonCompleted(false);
      return;
    }

    const fetchLessonDetails = async () => {
      try {
        const [mats, prog] = await Promise.all([
          getMaterialsByLessonApi(selectedLesson.id),
          getLessonProgressApi(selectedLesson.id).catch(() => null)
        ]);
        setMaterials(mats);
        setIsLessonCompleted(prog?.is_completed === 1);
      } catch (err) {
        console.warn("Error fetching lesson details");
      }
    };

    fetchLessonDetails();
  }, [selectedLesson?.id]);

  const toggleModuleExpand = (moduleId: number) => {
    if (expandedModuleIds.includes(moduleId)) {
      setExpandedModuleIds(expandedModuleIds.filter(id => id !== moduleId));
    } else {
      setExpandedModuleIds([...expandedModuleIds, moduleId]);
    }
  };

  const handleToggleLessonComplete = async () => {
    if (!selectedLesson) return;
    try {
      const newStatus = !isLessonCompleted;
      await saveLessonProgressApi(selectedLesson.id, newStatus, 300);
      setIsLessonCompleted(newStatus);
      setSuccessMsg(`Lesson marked as ${newStatus ? 'Completed' : 'Incomplete'}`);
      
      // Refresh summary progress
      if (selectedOfferingId) {
        const updatedProg = await getCourseProgressSummaryApi(selectedOfferingId);
        setCourseProgress(updatedProg);
      }
    } catch (err: any) {
      setError(err?.message || "Failed to save lesson progress");
    }
  };

  const handleCreateModuleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      await createModuleApi(selectedOfferingId, moduleForm);
      setSuccessMsg("Course module created successfully!");
      setShowModuleModal(false);
      setModuleForm({ title: "", description: "" });
      
      // Refresh modules
      const mods = await getModulesByOfferingApi(selectedOfferingId);
      setModules(mods);
    } catch (err: any) {
      setError(err?.message || "Failed to create course module");
    }
  };

  const handleCreateLessonSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      await createLessonApi(lessonForm.module_id, lessonForm);
      setSuccessMsg("Lesson created successfully!");
      setShowLessonModal(false);
      
      // Refresh module lessons
      const lns = await getLessonsByModuleApi(lessonForm.module_id);
      setLessonsMap(prev => ({ ...prev, [lessonForm.module_id]: lns }));
    } catch (err: any) {
      setError(err?.message || "Failed to create lesson");
    }
  };

  const handleCreateMaterialSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      const formData = new FormData();
      formData.append("title", materialForm.title);
      if (materialForm.external_url) {
        formData.append("external_url", materialForm.external_url);
      }
      if (selectedFile) {
        formData.append("file", selectedFile);
      }

      await createMaterialApi(materialForm.lesson_id, formData);
      setSuccessMsg("Learning material uploaded successfully!");
      setShowMaterialModal(false);
      setMaterialForm({ lesson_id: 0, title: "", external_url: "" });
      setSelectedFile(null);

      if (selectedLesson) {
        const mats = await getMaterialsByLessonApi(selectedLesson.id);
        setMaterials(mats);
      }
    } catch (err: any) {
      setError(err?.message || "Failed to upload learning material");
    }
  };

  const selectedOffering = offerings.find(o => o.id === selectedOfferingId);

  return (
    <motion.div
      key="learning-content-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="flex flex-col gap-6"
    >
      {/* Top Banner Header */}
      <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 className="text-xl font-bold text-white flex items-center gap-2 font-sans">
            <BookOpen className="w-6 h-6 text-teal-400" />
            Core LMS: Learning Content & Course Delivery Engine
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Course modules, structured text/video lessons, learning materials, and student progress tracking.
          </p>
        </div>

        <div className="flex items-center gap-2">
          {/* Course Selector Dropdown */}
          <select
            value={selectedOfferingId}
            onChange={(e) => setSelectedOfferingId(Number(e.target.value))}
            className="bg-slate-900 border border-slate-800 text-slate-200 text-xs font-semibold px-3 py-2 rounded-xl focus:outline-none focus:border-teal-500 font-sans"
          >
            {offerings.map((off) => (
              <option key={off.id} value={off.id}>
                {off.unit_code} - {off.unit_title} ({off.class_name})
              </option>
            ))}
          </select>

          {/* Mode Switcher */}
          <div className="bg-slate-900 border border-slate-800 p-1 rounded-xl flex items-center">
            <button
              onClick={() => setMode("learn")}
              className={`px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                mode === "learn" ? "bg-teal-500 text-slate-950" : "text-slate-400 hover:text-white"
              }`}
            >
              Learn View
            </button>
            {isLecturer && (
              <button
                onClick={() => setMode("manage")}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                  mode === "manage" ? "bg-teal-500 text-slate-950" : "text-slate-400 hover:text-white"
                }`}
              >
                Content Manager
              </button>
            )}
            {isLecturer && (
              <button
                onClick={() => setMode("analytics")}
                className={`px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                  mode === "analytics" ? "bg-teal-500 text-slate-950" : "text-slate-400 hover:text-white"
                }`}
              >
                Progress Overview
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Notifications */}
      {error && (
        <div className="bg-red-500/10 border border-red-500/30 p-4 rounded-xl text-xs text-red-300 flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-red-400 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {successMsg && (
        <div className="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl text-xs text-emerald-300 flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{successMsg}</span>
        </div>
      )}

      {/* Course Overview Header & Overall Progress Bar */}
      {selectedOffering && (
        <div className="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <div className="flex items-center gap-2">
              <span className="bg-teal-500/10 text-teal-400 font-mono text-[10px] font-bold px-2 py-0.5 rounded border border-teal-500/20">
                {selectedOffering.unit_code}
              </span>
              <h3 className="text-base font-bold text-white font-sans">{selectedOffering.unit_title}</h3>
            </div>
            <p className="text-xs text-slate-400 mt-1 font-mono">
              Class Cohort: {selectedOffering.class_name} ({selectedOffering.class_code}) • Primary Lecturer: {selectedOffering.lecturer_first_name ? `${selectedOffering.lecturer_first_name} ${selectedOffering.lecturer_last_name}` : 'Faculty Staff'}
            </p>
          </div>

          {courseProgress && (
            <div className="flex items-center gap-4 bg-slate-900 px-4 py-3 rounded-xl border border-slate-850 w-full md:w-auto">
              <div className="flex flex-col gap-1 w-full md:w-48">
                <div className="flex justify-between items-center text-[10px] font-mono">
                  <span className="text-slate-400">Course Completion</span>
                  <span className="text-teal-400 font-bold">{courseProgress.completion_percentage}%</span>
                </div>
                <div className="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                  <div 
                    className="bg-teal-400 h-2 rounded-full transition-all duration-500" 
                    style={{ width: `${courseProgress.completion_percentage}%` }} 
                  />
                </div>
              </div>

              <div className="text-right border-l border-slate-800 pl-4">
                <span className="text-xs font-bold text-white font-mono block">
                  {courseProgress.completed_lessons} / {courseProgress.total_lessons}
                </span>
                <span className="text-[10px] text-slate-500 font-mono">Lessons Done</span>
              </div>
            </div>
          )}
        </div>
      )}

      {/* MODE 1: STUDENT LEARNING VIEW */}
      {mode === "learn" && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Column: Module Accordion Sidebar (4 cols) */}
          <div className="lg:col-span-4 bg-slate-950 p-4 rounded-2xl border border-slate-800 flex flex-col gap-3">
            <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider font-mono px-2">
              Course Content Modules
            </h4>

            <div className="flex flex-col gap-2">
              {modules.length === 0 ? (
                <div className="p-6 text-center text-xs text-slate-500 font-mono">
                  No published learning modules available in this course offering.
                </div>
              ) : (
                modules.map((mod) => {
                  const isExpanded = expandedModuleIds.includes(mod.id);
                  const modLessons = lessonsMap[mod.id] || [];

                  return (
                    <div key={mod.id} className="bg-slate-900 rounded-xl border border-slate-850 overflow-hidden">
                      <button
                        onClick={() => toggleModuleExpand(mod.id)}
                        className="w-full p-3.5 flex justify-between items-center text-left hover:bg-slate-850/50 transition-all cursor-pointer"
                      >
                        <div>
                          <span className="text-xs font-bold text-white font-sans block">{mod.title}</span>
                          <span className="text-[10px] text-slate-500 font-mono block mt-0.5">
                            {modLessons.length} Lessons Available
                          </span>
                        </div>
                        {isExpanded ? (
                          <ChevronDown className="w-4 h-4 text-teal-400" />
                        ) : (
                          <ChevronRight className="w-4 h-4 text-slate-500" />
                        )}
                      </button>

                      {isExpanded && (
                        <div className="bg-slate-950 p-2 border-t border-slate-850 flex flex-col gap-1">
                          {modLessons.length === 0 ? (
                            <span className="text-[10px] text-slate-500 italic p-2 block">No published lessons inside module</span>
                          ) : (
                            modLessons.map((les) => {
                              const isSelected = selectedLesson?.id === les.id;
                              return (
                                <button
                                  key={les.id}
                                  onClick={() => setSelectedLesson(les)}
                                  className={`w-full p-2.5 rounded-lg text-left flex items-center justify-between transition-all cursor-pointer ${
                                    isSelected
                                      ? "bg-teal-500/10 text-teal-300 border border-teal-500/20"
                                      : "text-slate-400 hover:text-white hover:bg-slate-900"
                                  }`}
                                >
                                  <div className="flex items-center gap-2">
                                    {les.content_type === "video" ? (
                                      <Video className="w-3.5 h-3.5 text-teal-400 shrink-0" />
                                    ) : les.content_type === "quiz" || les.content_type === "assignment" ? (
                                      <HelpCircle className="w-3.5 h-3.5 text-amber-400 shrink-0" />
                                    ) : (
                                      <FileText className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    )}
                                    <span className="text-xs font-sans font-medium line-clamp-1">{les.title}</span>
                                  </div>
                                  <span className="text-[10px] text-slate-500 font-mono shrink-0 ml-2">{les.duration_minutes}m</span>
                                </button>
                              );
                            })
                          )}
                        </div>
                      )}
                    </div>
                  );
                })
              )}
            </div>
          </div>

          {/* Right Column: Lesson Viewer & Content Container (8 cols) */}
          <div className="lg:col-span-8 bg-slate-950 p-6 rounded-2xl border border-slate-800 flex flex-col gap-6">
            {selectedLesson ? (
              <div className="flex flex-col gap-6">
                {/* Lesson Title Bar */}
                <div className="flex justify-between items-start border-b border-slate-850 pb-4">
                  <div>
                    <span className="text-[10px] text-teal-400 font-mono uppercase tracking-wider block font-bold">
                      {selectedLesson.content_type.toUpperCase()} LESSON
                    </span>
                    <h2 className="text-lg font-bold text-white font-sans mt-1">{selectedLesson.title}</h2>
                  </div>

                  <button
                    onClick={handleToggleLessonComplete}
                    className={`px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all cursor-pointer ${
                      isLessonCompleted
                        ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                        : "bg-teal-500 hover:bg-teal-400 text-slate-950"
                    }`}
                  >
                    <CheckCircle2 className="w-4 h-4" />
                    <span>{isLessonCompleted ? "Completed" : "Mark as Complete"}</span>
                  </button>
                </div>

                {/* Lesson Content Viewer */}
                {selectedLesson.content_type === "quiz" || selectedLesson.content_type === "assignment" ? (
                  <div className="bg-amber-500/10 border border-amber-500/30 p-6 rounded-2xl flex flex-col gap-3">
                    <div className="flex items-center gap-2 text-amber-400 font-bold text-sm">
                      <HelpCircle className="w-5 h-5" />
                      <span>Planned Phase 6C Evaluation Engine</span>
                    </div>
                    <p className="text-xs text-amber-200/80 leading-relaxed font-sans">
                      This lesson contains a {selectedLesson.content_type} assessment placeholder. Interactive quizzes, automated scoring, brief submissions, and gradebook integration will be activated during Phase 6C.
                    </p>
                  </div>
                ) : (
                  <div className="prose prose-invert max-w-none text-slate-300 text-xs leading-relaxed font-sans whitespace-pre-wrap bg-slate-900 p-6 rounded-2xl border border-slate-850">
                    {selectedLesson.text_content || "No text content provided for this lesson."}
                  </div>
                )}

                {/* Learning Materials Downloads Section */}
                {materials.length > 0 && (
                  <div className="flex flex-col gap-3 border-t border-slate-850 pt-4">
                    <h4 className="text-xs font-bold text-white font-sans uppercase tracking-wider">
                      Attached Learning Materials & Resources ({materials.length})
                    </h4>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {materials.map((mat) => (
                        <div key={mat.id} className="bg-slate-900 p-3.5 rounded-xl border border-slate-850 flex justify-between items-center">
                          <div>
                            <span className="text-xs font-bold text-slate-200 block line-clamp-1">{mat.title}</span>
                            <span className="text-[10px] text-slate-500 font-mono block mt-0.5">
                              {mat.file_type} {mat.file_size_bytes ? `• ${(mat.file_size_bytes / 1024 / 1024).toFixed(1)} MB` : ''}
                            </span>
                          </div>

                          {mat.external_url ? (
                            <a
                              href={mat.external_url}
                              target="_blank"
                              rel="noreferrer"
                              className="bg-slate-800 hover:bg-slate-750 text-teal-400 p-2 rounded-lg transition-all"
                            >
                              <ExternalLink className="w-4 h-4" />
                            </a>
                          ) : (
                            <a
                              href={getMaterialDownloadUrl(mat.id)}
                              download
                              className="bg-teal-500/10 hover:bg-teal-500/20 text-teal-400 border border-teal-500/30 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all"
                            >
                              <Download className="w-3.5 h-3.5" />
                              <span>Download</span>
                            </a>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="py-16 text-center text-slate-500 font-mono text-xs">
                Select a module lesson from the left sidebar to start learning.
              </div>
            )}
          </div>
        </div>
      )}

      {/* MODE 2: LECTURER CONTENT MANAGEMENT VIEW */}
      {mode === "manage" && isLecturer && (
        <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 flex flex-col gap-6">
          <div className="flex justify-between items-center border-b border-slate-850 pb-4">
            <div>
              <h3 className="text-sm font-bold text-white font-sans uppercase tracking-wider">
                Lecturer Course Content Builder & Material Manager
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                Create structured modules, publish lessons, and attach learning materials for assigned courses.
              </p>
            </div>

            <button
              onClick={() => setShowModuleModal(true)}
              className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Create Course Module</span>
            </button>
          </div>

          <div className="flex flex-col gap-4">
            {modules.map((mod) => {
              const modLessons = lessonsMap[mod.id] || [];
              return (
                <div key={mod.id} className="bg-slate-900 p-5 rounded-2xl border border-slate-850 flex flex-col gap-4">
                  <div className="flex justify-between items-start">
                    <div>
                      <span className="text-[10px] text-teal-400 font-mono block font-bold">MODULE #{mod.sequence_order}</span>
                      <h4 className="text-sm font-bold text-white font-sans mt-0.5">{mod.title}</h4>
                      {mod.description && <p className="text-xs text-slate-400 mt-1">{mod.description}</p>}
                    </div>

                    <div className="flex items-center gap-2">
                      <button
                        onClick={() => {
                          setLessonForm({ ...lessonForm, module_id: mod.id });
                          setShowLessonModal(true);
                        }}
                        className="bg-slate-800 hover:bg-slate-750 text-teal-300 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition-all cursor-pointer"
                      >
                        <Plus className="w-3.5 h-3.5" />
                        <span>Add Lesson</span>
                      </button>
                    </div>
                  </div>

                  {/* Module Lessons Table */}
                  <div className="overflow-x-auto">
                    <table className="w-full text-left font-sans text-xs">
                      <thead>
                        <tr className="border-b border-slate-800 text-slate-500 font-mono">
                          <th className="pb-2">Lesson Title</th>
                          <th className="pb-2">Type</th>
                          <th className="pb-2">Duration</th>
                          <th className="pb-2">Status</th>
                          <th className="pb-2">Actions</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-850">
                        {modLessons.length === 0 ? (
                          <tr>
                            <td colSpan={5} className="py-4 text-slate-500 italic text-[11px]">
                              No lessons created in this module yet.
                            </td>
                          </tr>
                        ) : (
                          modLessons.map((les) => (
                            <tr key={les.id} className="hover:bg-slate-950/40">
                              <td className="py-2.5 text-slate-200 font-semibold">{les.title}</td>
                              <td className="py-2.5 font-mono text-teal-400 uppercase text-[10px]">{les.content_type}</td>
                              <td className="py-2.5 font-mono text-slate-400">{les.duration_minutes}m</td>
                              <td className="py-2.5">
                                <span className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold ${
                                  les.is_published ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" : "bg-slate-800 text-slate-400"
                                }`}>
                                  {les.is_published ? "Published" : "Draft"}
                                </span>
                              </td>
                              <td className="py-2.5 flex items-center gap-2">
                                <button
                                  onClick={() => {
                                    setMaterialForm({ ...materialForm, lesson_id: les.id });
                                    setShowMaterialModal(true);
                                  }}
                                  className="text-slate-400 hover:text-teal-400 text-[10px] font-mono underline cursor-pointer"
                                >
                                  + Attach File
                                </button>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* MODE 3: STUDENT PROGRESS OVERVIEW */}
      {mode === "analytics" && isLecturer && (
        <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 flex flex-col gap-4">
          <h3 className="text-sm font-bold text-white font-sans uppercase tracking-wider border-b border-slate-850 pb-3">
            Enrolled Student Completion Progress Ledger
          </h3>

          <div className="overflow-x-auto">
            <table className="w-full text-left font-sans text-xs">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 font-mono">
                  <th className="pb-3">Index Number</th>
                  <th className="pb-3">Student Name</th>
                  <th className="pb-3">Completed Lessons</th>
                  <th className="pb-3">Completion Percentage</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-850">
                {progressOverview.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-8 text-center text-slate-500">
                      No student progress records found for this course offering.
                    </td>
                  </tr>
                ) : (
                  progressOverview.map((item) => (
                    <tr key={item.student_id} className="hover:bg-slate-900/50">
                      <td className="py-3 font-mono text-teal-400">{item.index_number || 'N/A'}</td>
                      <td className="py-3">
                        <span className="font-bold text-white block">{item.first_name} {item.last_name}</span>
                        <span className="text-[10px] text-slate-500 font-mono block">{item.email}</span>
                      </td>
                      <td className="py-3 font-mono text-slate-200">
                        {item.completed_lessons} / {item.total_lessons}
                      </td>
                      <td className="py-3 font-mono">
                        <span className="text-teal-400 font-bold">{item.percentage}%</span>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* MODAL: CREATE MODULE */}
      {showModuleModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-slate-950 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl flex flex-col gap-4">
            <h3 className="text-base font-bold text-white font-sans">Create Course Module</h3>
            
            <form onSubmit={handleCreateModuleSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="block text-slate-400 mb-1">Module Title *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Module 3: Advanced Pneumatics & Hydraulics"
                  value={moduleForm.title}
                  onChange={(e) => setModuleForm({ ...moduleForm, title: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Module Description</label>
                <textarea
                  rows={3}
                  value={moduleForm.description}
                  onChange={(e) => setModuleForm({ ...moduleForm, description: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                />
              </div>

              <div className="flex justify-end gap-2 mt-3">
                <button
                  type="button"
                  onClick={() => setShowModuleModal(false)}
                  className="bg-slate-900 text-slate-400 hover:text-white px-4 py-2 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg cursor-pointer"
                >
                  Create Module
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: CREATE LESSON */}
      {showLessonModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-slate-950 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl flex flex-col gap-4">
            <h3 className="text-base font-bold text-white font-sans">Create Module Lesson</h3>
            
            <form onSubmit={handleCreateLessonSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="block text-slate-400 mb-1">Lesson Title *</label>
                <input
                  type="text"
                  required
                  value={lessonForm.title}
                  onChange={(e) => setLessonForm({ ...lessonForm, title: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Content Type</label>
                <select
                  value={lessonForm.content_type}
                  onChange={(e) => setLessonForm({ ...lessonForm, content_type: e.target.value as any })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white focus:outline-none focus:border-teal-500"
                >
                  <option value="text">Text / Reading Lesson</option>
                  <option value="video">Video Lesson</option>
                  <option value="document">Document / PDF Lesson</option>
                  <option value="quiz">Quiz Placeholder (Phase 6C)</option>
                  <option value="assignment">Assignment Placeholder (Phase 6C)</option>
                </select>
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Text Content / Instructions</label>
                <textarea
                  rows={4}
                  value={lessonForm.text_content}
                  onChange={(e) => setLessonForm({ ...lessonForm, text_content: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                />
              </div>

              <div className="flex justify-end gap-2 mt-3">
                <button
                  type="button"
                  onClick={() => setShowLessonModal(false)}
                  className="bg-slate-900 text-slate-400 hover:text-white px-4 py-2 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg cursor-pointer"
                >
                  Save Lesson
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: UPLOAD MATERIAL */}
      {showMaterialModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-slate-950 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl flex flex-col gap-4">
            <h3 className="text-base font-bold text-white font-sans">Upload Learning Material</h3>
            
            <form onSubmit={handleCreateMaterialSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="block text-slate-400 mb-1">Resource Title *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Safety Inspection Manual PDF"
                  value={materialForm.title}
                  onChange={(e) => setMaterialForm({ ...materialForm, title: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Upload File (PDF, DOCX, MP4 - Max 20MB)</label>
                <input
                  type="file"
                  onChange={(e) => setSelectedFile(e.target.files ? e.target.files[0] : null)}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-slate-300 font-mono focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">OR External URL Resource</label>
                <input
                  type="url"
                  placeholder="https://example.com/resource"
                  value={materialForm.external_url}
                  onChange={(e) => setMaterialForm({ ...materialForm, external_url: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-mono focus:outline-none focus:border-teal-500"
                />
              </div>

              <div className="flex justify-end gap-2 mt-3">
                <button
                  type="button"
                  onClick={() => setShowMaterialModal(false)}
                  className="bg-slate-900 text-slate-400 hover:text-white px-4 py-2 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg cursor-pointer"
                >
                  Upload Material
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </motion.div>
  );
}
