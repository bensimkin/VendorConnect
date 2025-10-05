import React from 'react';

/**
 * Converts URLs in text to clickable links
 * @param text - The text containing potential URLs
 * @returns React elements with URLs converted to links
 */
export function linkifyText(text: string): React.ReactNode {
  if (!text) return text;

  // Comprehensive URL regex pattern
  const urlRegex = /(https?:\/\/[^\s<]+[^<.,:;"')\]\s])/gi;
  
  const parts = text.split(urlRegex);
  
  return parts.map((part, index) => {
    if (part.match(urlRegex)) {
      return (
        <a
          key={index}
          href={part}
          target="_blank"
          rel="noopener noreferrer"
          className="text-blue-600 hover:text-blue-800 underline break-all"
          onClick={(e) => e.stopPropagation()}
        >
          {part}
        </a>
      );
    }
    return part;
  });
}

/**
 * Converts URLs in text to clickable links (for use in React components)
 * Returns JSX elements
 */
export function LinkifyContent({ children }: { children: string | null | undefined }) {
  if (!children) return null;
  
  return <>{linkifyText(children)}</>;
}
