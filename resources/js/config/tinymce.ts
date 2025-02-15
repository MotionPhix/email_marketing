export const defaultEditorConfig = {
  height: 500,
  menubar: true,
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
    'template', 'paste'
  ],
  toolbar: 'undo redo | formatselect | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | template | help',
  content_style: `
    body {
      font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      font-size: 14px;
      line-height: 1.6;
      margin: 0;
      padding: 1rem;
    }
  `,
  templates: [
    {
      title: 'Basic Newsletter',
      description: 'A simple newsletter template',
      content: `
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
          <h1>Newsletter Title</h1>
          <p>Welcome to our newsletter!</p>
          <!-- Add more default content -->
        </div>
      `
    }
  ],
  setup: (editor) => {
    // Add custom buttons or functionality here
    editor.on('init', () => {
      editor.setContent(editor.getContent())
    })
  }
}
