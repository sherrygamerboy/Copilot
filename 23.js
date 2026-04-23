const RichText = ({ content }) => {
  return (
    <div
      className="rich-text"
      dangerouslySetInnerHTML={{ __html: content }}
    />
  );
};
