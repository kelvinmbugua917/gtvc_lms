import React, { useState, useEffect } from "react";
import { 
  Bell, Megaphone, Plus, Filter, Search, ShieldCheck, AlertTriangle, 
  CheckCircle2, Clock, FileText, Download, UserCheck, Trash2, Eye, EyeOff, Archive, CheckCheck, RefreshCw, Send, Lock
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { useLms } from "../context/LmsContext";
import { announcementsApi, AnnouncementItem } from "../api/announcements";
import { notificationsApi, NotificationItem } from "../api/notifications";

export default function CommunicationPage() {
  const { currentUser, isLecturerOrAdmin, isStaff, isHOD } = useLms();

  const [activeTab, setActiveTab] = useState<"feed" | "manage" | "notifications">("feed");
  
  // Announcements State
  const [announcements, setAnnouncements] = useState<AnnouncementItem[]>([]);
  const [loadingAnnouncements, setLoadingAnnouncements] = useState<boolean>(true);
  const [searchQuery, setSearchQuery] = useState<string>("");
  const [selectedPriority, setSelectedPriority] = useState<string>("all");
  const [selectedAnnouncement, setSelectedAnnouncement] = useState<AnnouncementItem | null>(null);

  // Announcement Form State (For Staff/HOD/Admin)
  const [title, setTitle] = useState<string>("");
  const [content, setContent] = useState<string>("");
  const [priority, setPriority] = useState<"normal" | "important" | "urgent">("normal");
  const [targetRole, setTargetRole] = useState<string>("all");
  const [attachmentFile, setAttachmentFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);
  const [formSuccessMessage, setFormSuccessMessage] = useState<string | null>(null);
  const [formErrorMessage, setFormErrorMessage] = useState<string | null>(null);

  // Notifications State
  const [notifications, setNotifications] = useState<NotificationItem[]>([]);
  const [unreadCount, setUnreadCount] = useState<number>(0);
  const [loadingNotifications, setLoadingNotifications] = useState<boolean>(true);
  const [notificationFilter, setNotificationFilter] = useState<string>("all");

  // Initial Sample / Mock Data Fallback for Client Mode
  const defaultAnnouncements: AnnouncementItem[] = [
    {
      id: 101,
      title: "Opening of TVET Practical End-of-Term Trade Assessment Registration",
      content: "All Automotive, Electrical, and Building Technology diploma/certificate trainees are hereby notified that end-of-term trade practical assessments begin on August 10th, 2026. Please ensure your workshop safety gear and attendance register logbooks are submitted to your department HOD before Friday.",
      priority: "urgent",
      target_role: "student",
      author_user_id: 2,
      author_first_name: "Eng. Joseph",
      author_last_name: "Mwangi",
      author_email: "hod.electrical@gtvc.ac.ke",
      department_name: "Electrical & Electronics Engineering",
      department_code: "EEE",
      attachment_name: "GTVC_Trade_Assessment_Timetable_2026.pdf",
      attachment_path: "storage/uploads/materials/trade_assessment.pdf",
      is_published: 1,
      is_archived: 0,
      created_at: "2026-07-20 09:30:00",
      updated_at: "2026-07-20 09:30:00"
    },
    {
      id: 102,
      title: "KNEC Technical Examination Clearance & Fee Ledger Verification",
      content: "Student Finance & Registrar hereby remind candidates to clear pending tuition and KNEC examination fees. Official clearance dockets will be issued at the Registrar's Office upon verifying receipt records.",
      priority: "important",
      target_role: "all",
      author_user_id: 1,
      author_first_name: "Registrar",
      author_last_name: "Office",
      author_email: "registrar@gtvc.ac.ke",
      department_name: "Institutional Administration",
      department_code: "ADM",
      attachment_name: "KNEC_Fee_Structure_Notice.pdf",
      attachment_path: "storage/uploads/materials/knec_fees.pdf",
      is_published: 1,
      is_archived: 0,
      created_at: "2026-07-18 14:15:00",
      updated_at: "2026-07-18 14:15:00"
    },
    {
      id: 103,
      title: "Faculty & Trainer Workshop Maintenance Protocol Training",
      content: "All TVET workshop trainers and lab technologists are requested to attend the mandatory equipment safety protocol workshop in Workshop Block B on Thursday at 10:00 AM.",
      priority: "normal",
      target_role: "staff",
      author_user_id: 3,
      author_first_name: "Dr. Catherine",
      author_last_name: "Kariuki",
      author_email: "principal@gtvc.ac.ke",
      department_name: "Academic Affairs",
      department_code: "ACA",
      is_published: 1,
      is_archived: 0,
      created_at: "2026-07-15 11:00:00",
      updated_at: "2026-07-15 11:00:00"
    }
  ];

  const defaultNotifications: NotificationItem[] = [
    {
      id: 1,
      user_id: currentUser?.id || 1,
      type: "attendance_warning",
      title: "CRITICAL ATTENDANCE ALERT: Exam Disqualification Risk",
      message: "Your calculated attendance for unit 'EET 201 Electrical Machines I' is currently 58.3%. This is below the 60% requirement. You are at risk of exam debarment.",
      priority: "urgent",
      related_entity_type: "attendance",
      related_entity_id: 1,
      is_read: 0,
      created_at: "2026-07-21 16:45:00"
    },
    {
      id: 2,
      user_id: currentUser?.id || 1,
      type: "academic_assignment",
      title: "New Assignment Released",
      message: "Assignment 'Practical Circuit Wiring & Load Balancing Project' is now open. Deadline: Aug 5, 2026 23:59.",
      priority: "important",
      related_entity_type: "assignment",
      related_entity_id: 12,
      is_read: 0,
      created_at: "2026-07-20 11:30:00"
    },
    {
      id: 3,
      user_id: currentUser?.id || 1,
      type: "academic_grade",
      title: "Assignment Submission Graded",
      message: "Your submission for 'Mid-Term Motor Winding Calculations' has been graded: 88/100 marks.",
      priority: "normal",
      related_entity_type: "assignment",
      related_entity_id: 11,
      is_read: 1,
      read_at: "2026-07-19 10:20:00",
      created_at: "2026-07-19 09:15:00"
    },
    {
      id: 4,
      user_id: currentUser?.id || 1,
      type: "announcement",
      title: "New Announcement: Opening of TVET Trade Assessment Registration",
      message: "All Automotive, Electrical, and Building Technology diploma/certificate trainees are hereby notified...",
      priority: "urgent",
      related_entity_type: "announcement",
      related_entity_id: 101,
      is_read: 0,
      created_at: "2026-07-20 09:30:00"
    }
  ];

  const fetchAnnouncements = async () => {
    setLoadingAnnouncements(true);
    try {
      const data = await announcementsApi.getAnnouncements({
        search: searchQuery,
        priority: selectedPriority !== "all" ? selectedPriority : undefined,
        management_view: activeTab === "manage"
      });
      if (Array.isArray(data) && data.length > 0) {
        setAnnouncements(data);
      } else {
        setAnnouncements(defaultAnnouncements);
      }
    } catch {
      setAnnouncements(defaultAnnouncements);
    } finally {
      setLoadingAnnouncements(false);
    }
  };

  const fetchNotifications = async () => {
    setLoadingNotifications(true);
    try {
      const res = await notificationsApi.getNotifications({
        unread_only: notificationFilter === "unread",
        type: notificationFilter !== "all" && notificationFilter !== "unread" ? notificationFilter : undefined
      });
      if (res && Array.isArray(res.notifications)) {
        setNotifications(res.notifications);
        setUnreadCount(res.unread_count || 0);
      } else {
        setNotifications(defaultNotifications);
        setUnreadCount(defaultNotifications.filter(n => !n.is_read).length);
      }
    } catch {
      setNotifications(defaultNotifications);
      setUnreadCount(defaultNotifications.filter(n => !n.is_read).length);
    } finally {
      setLoadingNotifications(false);
    }
  };

  useEffect(() => {
    fetchAnnouncements();
    fetchNotifications();
  }, [searchQuery, selectedPriority, activeTab, notificationFilter]);

  // Handle Announcement Creation
  const handleCreateAnnouncement = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !content.trim()) {
      setFormErrorMessage("Title and content are required.");
      return;
    }

    setIsSubmitting(true);
    setFormSuccessMessage(null);
    setFormErrorMessage(null);

    try {
      const formData = new FormData();
      formData.append("title", title);
      formData.append("content", content);
      formData.append("priority", priority);
      formData.append("target_role", targetRole);
      formData.append("is_published", "1");
      if (attachmentFile) {
        formData.append("attachment", attachmentFile);
      }

      await announcementsApi.createAnnouncement(formData);

      setFormSuccessMessage("Announcement published successfully!");
      setTitle("");
      setContent("");
      setPriority("normal");
      setTargetRole("all");
      setAttachmentFile(null);
      fetchAnnouncements();
    } catch (err: any) {
      setFormErrorMessage(err.message || "Failed to publish announcement.");
    } finally {
      setIsSubmitting(false);
    }
  };

  // Handle Mark as Read
  const handleMarkAsRead = async (id: number) => {
    try {
      await notificationsApi.markAsRead(id);
      setNotifications(prev => prev.map(n => n.id === id ? { ...n, is_read: 1 } : n));
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch {
      setNotifications(prev => prev.map(n => n.id === id ? { ...n, is_read: 1 } : n));
      setUnreadCount(prev => Math.max(0, prev - 1));
    }
  };

  // Handle Mark All as Read
  const handleMarkAllAsRead = async () => {
    try {
      await notificationsApi.markAllAsRead();
      setNotifications(prev => prev.map(n => ({ ...n, is_read: 1 })));
      setUnreadCount(0);
    } catch {
      setNotifications(prev => prev.map(n => ({ ...n, is_read: 1 })));
      setUnreadCount(0);
    }
  };

  // Handle Delete Notification
  const handleDeleteNotification = async (id: number) => {
    try {
      await notificationsApi.deleteNotification(id);
      setNotifications(prev => prev.filter(n => n.id !== id));
    } catch {
      setNotifications(prev => prev.filter(n => n.id !== id));
    }
  };

  return (
    <div className="space-y-6 pb-12">
      {/* Header Banner */}
      <div className="relative overflow-hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
        <div className="absolute -right-12 -top-12 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none" />
        <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-2">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-400 text-xs font-semibold uppercase tracking-wider">
              <Megaphone className="w-3.5 h-3.5" /> Phase 6E Communication & Notifications
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
              Institutional Communications Hub
            </h1>
            <p className="text-slate-400 text-sm max-w-2xl">
              Targeted announcements, official circulars, and real-time in-app notification alerts for Gilgil Technical and Vocational College trainees and faculty.
            </p>
          </div>

          {/* Quick Notification Stats */}
          <div className="flex items-center gap-3 self-start md:self-auto">
            <button
              onClick={() => setActiveTab("notifications")}
              className="relative flex items-center gap-3 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 px-4 py-3 rounded-xl transition-all cursor-pointer group"
            >
              <div className="relative">
                <Bell className="w-5 h-5 text-teal-400 group-hover:scale-110 transition-transform" />
                {unreadCount > 0 && (
                  <span className="absolute -top-1.5 -right-1.5 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse">
                    {unreadCount}
                  </span>
                )}
              </div>
              <div className="text-left">
                <div className="text-xs text-slate-400 font-medium">Unread Alerts</div>
                <div className="text-sm font-bold text-white">{unreadCount} New</div>
              </div>
            </button>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="flex items-center gap-2 mt-8 pt-6 border-t border-slate-800/80 overflow-x-auto">
          <button
            onClick={() => setActiveTab("feed")}
            className={`flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap ${
              activeTab === "feed"
                ? "bg-teal-500 text-slate-950 shadow-lg shadow-teal-500/10 font-bold"
                : "text-slate-400 hover:text-white hover:bg-slate-800/60"
            }`}
          >
            <Megaphone className="w-4 h-4" />
            <span>Announcements Feed</span>
          </button>

          {(isLecturerOrAdmin || isStaff || isHOD) && (
            <button
              onClick={() => setActiveTab("manage")}
              className={`flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap ${
                activeTab === "manage"
                  ? "bg-teal-500 text-slate-950 shadow-lg shadow-teal-500/10 font-bold"
                  : "text-slate-400 hover:text-white hover:bg-slate-800/60"
              }`}
            >
              <Plus className="w-4 h-4" />
              <span>Create & Manage Notices</span>
            </button>
          )}

          <button
            onClick={() => setActiveTab("notifications")}
            className={`flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap ${
              activeTab === "notifications"
                ? "bg-teal-500 text-slate-950 shadow-lg shadow-teal-500/10 font-bold"
                : "text-slate-400 hover:text-white hover:bg-slate-800/60"
            }`}
          >
            <Bell className="w-4 h-4" />
            <span>Notification Center</span>
            {unreadCount > 0 && (
              <span className="bg-rose-500/20 text-rose-400 px-1.5 py-0.5 rounded-full text-[10px] font-bold border border-rose-500/30">
                {unreadCount}
              </span>
            )}
          </button>
        </div>
      </div>

      {/* TAB 1: ANNOUNCEMENTS FEED */}
      {activeTab === "feed" && (
        <div className="space-y-6">
          {/* Filters Bar */}
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div className="relative w-full sm:w-80">
              <Search className="w-4 h-4 absolute left-3 top-3 text-slate-400" />
              <input
                type="text"
                placeholder="Search notices, title, content..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full bg-slate-950 border border-slate-800 rounded-lg pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 transition-colors"
              />
            </div>

            <div className="flex items-center gap-3 w-full sm:w-auto">
              <div className="flex items-center gap-2 text-xs text-slate-400">
                <Filter className="w-3.5 h-3.5" /> Priority:
              </div>
              <div className="flex items-center gap-1.5 bg-slate-950 p-1 rounded-lg border border-slate-800">
                {["all", "normal", "important", "urgent"].map((p) => (
                  <button
                    key={p}
                    onClick={() => setSelectedPriority(p)}
                    className={`px-3 py-1 rounded text-[11px] font-medium capitalize transition-all cursor-pointer ${
                      selectedPriority === p
                        ? "bg-slate-800 text-white font-bold"
                        : "text-slate-400 hover:text-white"
                    }`}
                  >
                    {p}
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Feed List */}
          {loadingAnnouncements ? (
            <div className="p-12 text-center text-slate-400 text-sm bg-slate-900 border border-slate-800 rounded-xl">
              <RefreshCw className="w-6 h-6 animate-spin mx-auto text-teal-400 mb-2" />
              Loading institutional announcements...
            </div>
          ) : announcements.length === 0 ? (
            <div className="p-12 text-center bg-slate-900 border border-slate-800 rounded-xl space-y-3">
              <Megaphone className="w-10 h-10 text-slate-600 mx-auto" />
              <h3 className="text-base font-semibold text-white">No Announcements Found</h3>
              <p className="text-xs text-slate-400">No active circulars match your selected search criteria.</p>
            </div>
          ) : (
            <div className="space-y-4">
              {announcements.map((item) => {
                const isUrgent = item.priority === "urgent";
                const isImportant = item.priority === "important";

                return (
                  <motion.div
                    key={item.id}
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className={`bg-slate-900 border rounded-2xl p-6 transition-all hover:border-slate-700 ${
                      isUrgent
                        ? "border-rose-500/40 bg-gradient-to-r from-slate-900 via-slate-900 to-rose-950/10"
                        : isImportant
                        ? "border-amber-500/40"
                        : "border-slate-800"
                    }`}
                  >
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                      <div className="flex items-center gap-2.5 flex-wrap">
                        {/* Priority Badge */}
                        <span
                          className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase ${
                            isUrgent
                              ? "bg-rose-500/20 text-rose-400 border border-rose-500/30"
                              : isImportant
                              ? "bg-amber-500/20 text-amber-400 border border-amber-500/30"
                              : "bg-teal-500/10 text-teal-400 border border-teal-500/20"
                          }`}
                        >
                          {isUrgent && <AlertTriangle className="w-3 h-3" />}
                          {item.priority} Priority
                        </span>

                        {/* Department Badge */}
                        {item.department_name && (
                          <span className="bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-1 rounded-full text-[11px] font-medium">
                            {item.department_name}
                          </span>
                        )}

                        <span className="text-xs text-slate-500 flex items-center gap-1">
                          <Clock className="w-3 h-3" />
                          {new Date(item.created_at).toLocaleDateString("en-KE", {
                            year: "numeric",
                            month: "short",
                            day: "numeric"
                          })}
                        </span>
                      </div>

                      <div className="text-xs text-slate-400">
                        Issued by: <span className="text-white font-medium">{item.author_first_name} {item.author_last_name}</span>
                      </div>
                    </div>

                    <h2 className="text-lg font-bold text-white mb-2 leading-snug">
                      {item.title}
                    </h2>

                    <p className="text-slate-300 text-sm leading-relaxed whitespace-pre-line mb-4">
                      {item.content}
                    </p>

                    {/* Attachment Footer */}
                    {item.attachment_name && (
                      <div className="pt-4 border-t border-slate-800 flex items-center justify-between gap-4">
                        <div className="flex items-center gap-2.5 text-xs text-slate-300 bg-slate-950 border border-slate-800 px-3.5 py-2 rounded-xl">
                          <FileText className="w-4 h-4 text-teal-400" />
                          <span className="truncate max-w-xs">{item.attachment_name}</span>
                        </div>

                        <a
                          href={announcementsApi.getDownloadUrl(item.id)}
                          download
                          className="inline-flex items-center gap-1.5 bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-bold px-3.5 py-2 rounded-xl transition-all cursor-pointer shadow-md shadow-teal-500/10"
                        >
                          <Download className="w-3.5 h-3.5" /> Download PDF Notice
                        </a>
                      </div>
                    )}
                  </motion.div>
                );
              })}
            </div>
          )}
        </div>
      )}

      {/* TAB 2: CREATE & MANAGE ANNOUNCEMENTS */}
      {activeTab === "manage" && (isLecturerOrAdmin || isStaff || isHOD) && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Create Announcement Form */}
          <div className="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
            <div className="flex items-center gap-2 text-white font-bold text-base pb-3 border-b border-slate-800">
              <Plus className="w-4 h-4 text-teal-400" /> Compose Institutional Notice
            </div>

            {formSuccessMessage && (
              <div className="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs p-3 rounded-xl flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 shrink-0" />
                <span>{formSuccessMessage}</span>
              </div>
            )}

            {formErrorMessage && (
              <div className="bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs p-3 rounded-xl flex items-center gap-2">
                <AlertTriangle className="w-4 h-4 shrink-0" />
                <span>{formErrorMessage}</span>
              </div>
            )}

            <form onSubmit={handleCreateAnnouncement} className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Notice Title *
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g., End of Term Assessment Schedule"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-teal-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Target Audience *
                </label>
                <select
                  value={targetRole}
                  onChange={(e) => setTargetRole(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-teal-500"
                >
                  <option value="all">All GTVC Users (Students & Staff)</option>
                  <option value="student">Enrolled Trainees / Students Only</option>
                  <option value="staff">Lecturers, Trainers & Staff Only</option>
                  <option value="hod">Department HODs Only</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Priority Level *
                </label>
                <select
                  value={priority}
                  onChange={(e) => setPriority(e.target.value as any)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-teal-500"
                >
                  <option value="normal">Normal Priority</option>
                  <option value="important">Important Priority</option>
                  <option value="urgent">Urgent / Critical Priority</option>
                </select>
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  Notice Body / Content *
                </label>
                <textarea
                  required
                  rows={5}
                  placeholder="Write full text of the circular notice..."
                  value={content}
                  onChange={(e) => setContent(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-teal-500 resize-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium text-slate-400 mb-1">
                  PDF / Document Attachment (Optional)
                </label>
                <input
                  type="file"
                  accept=".pdf,.docx,.jpg,.png"
                  onChange={(e) => setAttachmentFile(e.target.files?.[0] || null)}
                  className="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-teal-400 hover:file:bg-slate-700"
                />
              </div>

              <button
                type="submit"
                disabled={isSubmitting}
                className="w-full bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs py-3 rounded-xl transition-all cursor-pointer flex items-center justify-center gap-2 shadow-lg shadow-teal-500/10"
              >
                {isSubmitting ? (
                  <RefreshCw className="w-4 h-4 animate-spin" />
                ) : (
                  <>
                    <Send className="w-4 h-4" /> Publish Announcement Now
                  </>
                )}
              </button>
            </form>
          </div>

          {/* Published Announcements Management Table */}
          <div className="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
              <h3 className="font-bold text-white text-base">Active & Archived Circulars Ledger</h3>
              <span className="text-xs text-slate-400">{announcements.length} Records</span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <th className="py-3 px-3">Title & Author</th>
                    <th className="py-3 px-3">Target</th>
                    <th className="py-3 px-3">Priority</th>
                    <th className="py-3 px-3">Date</th>
                    <th className="py-3 px-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60 text-xs">
                  {announcements.map((item) => (
                    <tr key={item.id} className="hover:bg-slate-800/40 transition-colors">
                      <td className="py-3 px-3">
                        <div className="font-bold text-white">{item.title}</div>
                        <div className="text-[11px] text-slate-500">{item.author_first_name} {item.author_last_name}</div>
                      </td>
                      <td className="py-3 px-3">
                        <span className="bg-slate-800 text-slate-300 px-2 py-0.5 rounded text-[10px] uppercase font-bold">
                          {item.target_role}
                        </span>
                      </td>
                      <td className="py-3 px-3">
                        <span className={`px-2 py-0.5 rounded text-[10px] font-bold capitalize ${
                          item.priority === 'urgent' ? 'bg-rose-500/20 text-rose-400' :
                          item.priority === 'important' ? 'bg-amber-500/20 text-amber-400' : 'bg-teal-500/20 text-teal-400'
                        }`}>
                          {item.priority}
                        </span>
                      </td>
                      <td className="py-3 px-3 text-slate-400 text-[11px]">
                        {new Date(item.created_at).toLocaleDateString()}
                      </td>
                      <td className="py-3 px-3 text-right space-x-1">
                        <button
                          onClick={async () => {
                            await announcementsApi.archiveAnnouncement(item.id);
                            fetchAnnouncements();
                          }}
                          title="Archive Notice"
                          className="p-1.5 rounded bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer"
                        >
                          <Archive className="w-3.5 h-3.5" />
                        </button>
                        <button
                          onClick={async () => {
                            await announcementsApi.deleteAnnouncement(item.id);
                            fetchAnnouncements();
                          }}
                          title="Delete Notice"
                          className="p-1.5 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-colors cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* TAB 3: NOTIFICATION CENTER */}
      {activeTab === "notifications" && (
        <div className="space-y-6">
          {/* Notification Header Bar */}
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div className="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
              {[
                { id: "all", label: "All Alerts" },
                { id: "unread", label: "Unread Only" },
                { id: "academic_assignment", label: "Assignments" },
                { id: "academic_grade", label: "Grades" },
                { id: "attendance_warning", label: "Attendance Warnings" },
                { id: "announcement", label: "Announcements" }
              ].map((f) => (
                <button
                  key={f.id}
                  onClick={() => setNotificationFilter(f.id)}
                  className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer whitespace-nowrap ${
                    notificationFilter === f.id
                      ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10"
                      : "bg-slate-950 text-slate-400 hover:text-white border border-slate-800"
                  }`}
                >
                  {f.label}
                </button>
              ))}
            </div>

            <button
              onClick={handleMarkAllAsRead}
              className="inline-flex items-center gap-1.5 text-xs text-teal-400 hover:text-teal-300 font-semibold bg-teal-500/10 border border-teal-500/20 px-3.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0"
            >
              <CheckCheck className="w-3.5 h-3.5" /> Mark All as Read
            </button>
          </div>

          {/* Notifications List */}
          {loadingNotifications ? (
            <div className="p-12 text-center text-slate-400 text-sm bg-slate-900 border border-slate-800 rounded-xl">
              <RefreshCw className="w-6 h-6 animate-spin mx-auto text-teal-400 mb-2" />
              Loading notification history...
            </div>
          ) : notifications.length === 0 ? (
            <div className="p-12 text-center bg-slate-900 border border-slate-800 rounded-xl space-y-3">
              <Bell className="w-10 h-10 text-slate-600 mx-auto" />
              <h3 className="text-base font-semibold text-white">No Notifications Available</h3>
              <p className="text-xs text-slate-400">You are up to date! No unread notifications or security alerts found.</p>
            </div>
          ) : (
            <div className="space-y-3">
              {notifications.map((n) => {
                const isUnread = !n.is_read;
                const isAttendance = n.type === "attendance_warning";
                const isAcademic = n.type.startsWith("academic");
                const isAnnouncement = n.type === "announcement";

                return (
                  <motion.div
                    key={n.id}
                    initial={{ opacity: 0, y: 5 }}
                    animate={{ opacity: 1, y: 0 }}
                    className={`bg-slate-900 border rounded-xl p-4 transition-all flex items-start gap-4 ${
                      isUnread
                        ? "border-teal-500/40 bg-slate-900/90 shadow-lg shadow-teal-500/5"
                        : "border-slate-800/80 opacity-80"
                    }`}
                  >
                    {/* Event Icon */}
                    <div className={`p-2.5 rounded-xl shrink-0 ${
                      isAttendance ? "bg-rose-500/20 text-rose-400" :
                      isAcademic ? "bg-sky-500/20 text-sky-400" :
                      isAnnouncement ? "bg-teal-500/20 text-teal-400" : "bg-slate-800 text-slate-400"
                    }`}>
                      {isAttendance && <AlertTriangle className="w-5 h-5" />}
                      {isAcademic && <FileText className="w-5 h-5" />}
                      {isAnnouncement && <Megaphone className="w-5 h-5" />}
                      {!isAttendance && !isAcademic && !isAnnouncement && <Bell className="w-5 h-5" />}
                    </div>

                    {/* Content */}
                    <div className="flex-1 min-w-0 space-y-1">
                      <div className="flex items-center justify-between gap-2">
                        <h4 className={`text-sm font-bold ${isUnread ? "text-white" : "text-slate-300"}`}>
                          {n.title}
                        </h4>
                        <span className="text-[11px] text-slate-500 shrink-0">
                          {new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                        </span>
                      </div>

                      <p className="text-xs text-slate-400 leading-relaxed">
                        {n.message}
                      </p>

                      <div className="pt-2 flex items-center justify-between text-[11px]">
                        <span className="text-slate-500 capitalize">
                          Category: {n.type.replace('_', ' ')}
                        </span>

                        <div className="flex items-center gap-2">
                          {isUnread ? (
                            <button
                              onClick={() => handleMarkAsRead(n.id)}
                              className="text-teal-400 hover:text-teal-300 font-semibold cursor-pointer"
                            >
                              Mark as Read
                            </button>
                          ) : (
                            <span className="text-slate-600 flex items-center gap-1">
                              <CheckCircle2 className="w-3 h-3 text-emerald-500" /> Read
                            </span>
                          )}

                          <button
                            onClick={() => handleDeleteNotification(n.id)}
                            className="text-slate-500 hover:text-rose-400 transition-colors cursor-pointer"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                );
              })}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
