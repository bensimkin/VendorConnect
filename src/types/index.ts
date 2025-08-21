// User types
export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  photo?: string;
  status: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
  roles?: Role[];
  permissions?: Permission[];
}

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at: string;
}

// Task types
export interface Task {
  id: number;
  title: string;
  description?: string;
  status_id: number;
  priority_id: number;
  task_type_id: number;
  start_date: string;
  end_date: string;
  note?: string;
  close_deadline: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  status?: Status;
  priority?: Priority;
  taskType?: TaskType;
  users?: User[];
  clients?: Client[];
  tags?: Tag[];
}

export interface TaskType {
  id: number;
  task_type: string;
  created_at: string;
  updated_at: string;
}

export interface Status {
  id: number;
  name: string;
  color?: string;
  created_at: string;
  updated_at: string;
}

export interface Priority {
  id: number;
  name: string;
  color?: string;
  created_at: string;
  updated_at: string;
}

// Client types
export interface Client {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  company?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip?: string;
  website?: string;
  notes?: string;
  status: boolean;
  photo?: string;
  created_at: string;
  updated_at: string;
}

// Tag types
export interface Tag {
  id: number;
  title: string;
  slug: string;
  created_at: string;
  updated_at: string;
}

// Project types
export interface Project {
  id: number;
  name: string;
  description?: string;
  status_id: number;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
  status?: Status;
  tasks?: Task[];
}

// Dashboard types
export interface DashboardData {
  total_tasks: number;
  total_users: number;
  total_clients: number;
  total_projects: number;
  recent_tasks: Task[];
  task_statistics: {
    pending: number;
    in_progress: number;
    completed: number;
    overdue: number;
  };
  user_activity: UserActivity[];
  task_completion_trend: TaskCompletionTrend[];
}

export interface UserActivity {
  user_id: number;
  user_name: string;
  recent_tasks: number;
  completed_tasks: number;
}

export interface TaskCompletionTrend {
  date: string;
  completed: number;
  total: number;
}

// Notification types
export interface Notification {
  id: number;
  title: string;
  message: string;
  type: string;
  read_at?: string;
  created_at: string;
  updated_at: string;
}

// Auth types
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

// Form types
export interface CreateTaskData {
  title: string;
  description?: string;
  task_type_id: number;
  start_date: string;
  end_date: string;
  status_id: number;
  priority_id: number;
  note?: string;
  user_ids?: number[];
  client_ids?: number[];
  tag_ids?: number[];
}

export interface UpdateTaskData extends Partial<CreateTaskData> {
  id: number;
}
