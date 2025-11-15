import { useAuthStore } from '@/lib/auth-store';

export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  photo: string | null;
  status: number;
  roles?: Array<{ id: number; name: string }>;
  notification_preferences?: {
    email_notifications?: boolean;
    push_notifications?: boolean;
    task_assignments?: boolean;
    task_updates?: boolean;
    project_updates?: boolean;
    mentions?: boolean;
  };
}

/**
 * Check if the current user has admin or sub-admin privileges
 */
export const hasAdminPrivileges = (user: User | null): boolean => {
  if (!user?.roles) return false;
  
  const adminRoles = ['admin', 'sub admin'];
  return user.roles.some(role => 
    adminRoles.includes(role.name.toLowerCase())
  );
};

/**
 * Filter sensitive client data based on user role
 */
export const filterSensitiveClientData = (client: any, user: User | null): any => {
  if (!client) return client;
  
  // If user has admin privileges, return full data
  if (hasAdminPrivileges(user)) {
    return client;
  }
  
  // For Requesters and Taskers, remove sensitive information
  const filteredClient = { ...client };
  
  // Remove sensitive fields
  delete filteredClient.email;
  delete filteredClient.phone;
  delete filteredClient.address;
  delete filteredClient.city;
  delete filteredClient.state;
  delete filteredClient.country;
  delete filteredClient.zip;
  delete filteredClient.dob;
  delete filteredClient.website;
  delete filteredClient.notes;
  
  return filteredClient;
};

/**
 * Filter sensitive data from an array of clients
 */
export const filterSensitiveClientDataArray = (clients: any[], user: User | null): any[] => {
  if (!Array.isArray(clients)) return clients;
  
  return clients.map(client => filterSensitiveClientData(client, user));
};
