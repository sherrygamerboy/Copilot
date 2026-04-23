import React from "react";
import DOMPurify from "dompurify";

export default function SafeUserContent({ html }) {
  // Sanitize incoming HTML to neutralize scripts, event handlers, and injections
  const clean = DOMPurify.sanitize(html || "");

  return (
    <div
      className="user-content"
      dangerouslySetInnerHTML={{ __html: clean }}
    />
  );
}
