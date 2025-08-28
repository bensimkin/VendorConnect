/**
 * Format role names for display in the UI
 * Converts database role names to proper case
 */
export function formatRoleName(roleName: string): string {
  if (!roleName) return '';
  
  // Special cases for known roles
  const roleMap: Record<string, string> = {
    'admin': 'Admin',
    'sub_admin': 'Sub Admin',
    'sub admin': 'Sub Admin',
    'requester': 'Requester',
    'tasker': 'Tasker',
  };
  
  // Check if we have a specific mapping
  const normalizedRole = roleName.toLowerCase().trim();
  if (roleMap[normalizedRole]) {
    return roleMap[normalizedRole];
  }
  
  // Otherwise, capitalize each word
  return roleName
    .split(/[\s_]+/) // Split by spaces or underscores
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
}


