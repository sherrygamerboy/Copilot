import React from "react";
import DOMPurify from "dompurify";

export default function UserDescription({ userDescription }) {
  // Sanitize incoming HTML to prevent XSS
  const cleanHtml = DOMPurify.sanitize(userDescription || "");

  return (
    <div
      className="user-description"
      dangerouslySetInnerHTML={{ __html: cleanHtml }}
    />
  );
}
