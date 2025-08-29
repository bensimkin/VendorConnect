export const getFileUrl = (url: string): string => {
  if (!url) return '';
  const base = process.env.NEXT_PUBLIC_API_URL?.replace(/\/api\/v1$/, '') || '';
  return url.startsWith('http') ? url : `${base}${url}`;
};

export default getFileUrl;
