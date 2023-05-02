<div>
  <style>
    .ck-editor__main {
      /* 子元素并排 */
      display: flex;
    }

    .ck-editor__editable {
      /* 平分子盒子 */
      flex: 1;
    }
  </style>
  {{-- 标题 --}}
  @if ($title ?? false)
    <div class="p-2 bg-sky">
      <details>
        <summary>{{ $title }}</summary>
        <p class="alert mb-0">
          您可以在下面的编辑框里使用Latex公式。示例：<br>
          · 行内公式：$f(x)=x^2$（显示效果为<span class="math_formula">$f(x)=x^2$</span>）<br>
          · 单行居中：$$f(x)=x^2$$（显示效果如下）<span class="math_formula">$$f(x)=x^2$$</span><br>
        </p>
      </details>
    </div>
  @endif

  {{-- 编辑框实体 --}}
  <div id="div_{{ $domId }}" class="position-relative">
    <textarea id="{{ $domId }}" name="{{ $name }}">{{ $content }}</textarea>
    <a href="javascript:" class="position-absolute" style="top: 2.6rem; right: 0.2rem; font-size:0.6rem"
      onclick="window['{{ $name }}_preview'].toggle()">预览</a>
  </div>

  {{-- 生成编辑器。前提：务必在布局中引入ckeditor.js --}}
  <script>
    $(function() {
      // ckeditor5配置
      const ck5_config = {
        // removePlugins: ['FontBackgroundColor', 'mediaEmbed'],
        // language: 'zh-cn',
        ckfinder: {
          uploadUrl: "{{ route('api.ckeditor_files') }}"
        },
        toolbar: {
          items: ["heading", "bold", "italic", "underline", "horizontalLine", "fontColor", "fontBackgroundColor",
            "highlight", "|", "alignment", "outdent", "indent", "numberedList", "bulletedList", "todoList", "|",
            "code", "codeBlock", "link", "blockQuote", "pageBreak", "insertTable", "imageUpload",
            // "mediaEmbed",
            "|", "removeFormat", "undo", "redo"
          ]
        },
        heading: {
          options: [{
              model: 'paragraph',
              title: 'Paragraph',
              class: 'ck-heading_paragraph'
            },
            {
              model: 'heading1',
              view: 'h1',
              title: 'Heading 1',
              class: 'ck-heading_heading1'
            },
            {
              model: 'heading2',
              view: 'h2',
              title: 'Heading 2',
              class: 'ck-heading_heading2'
            },
            {
              model: 'heading3',
              view: 'h3',
              title: 'Heading 3',
              class: 'ck-heading_heading3'
            },
            {
              model: 'heading4',
              view: 'h4',
              title: 'Heading 4',
              class: 'ck-heading_heading4'
            },
            {
              model: 'heading5',
              view: 'h5',
              title: 'Heading 5',
              class: 'ck-heading_heading5'
            },
            {
              model: 'heading6',
              view: 'h6',
              title: 'Heading 6',
              class: 'ck-heading_heading6'
            }
          ]
        },
        image: {
          // resizeUnit: 'rem',
          resizeOptions: [{
              name: 'resizeImage:S1',
              value: '20',
              icon: 'small'
            },
            {
              name: 'resizeImage:S2',
              value: '50',
              icon: 'small'
            },
            {
              name: 'resizeImage:S3',
              value: '75',
              icon: 'medium'
            },
            {
              name: 'resizeImage:original',
              value: null,
              icon: 'original'
            },
            {
              name: 'resizeImage:L1',
              value: '150',
              icon: 'large'
            },
            {
              name: 'resizeImage:L2',
              value: '200',
              icon: 'large'
            }
          ],
          toolbar: [
            'imageTextAlternative', 'toggleImageCaption', 'imageStyle:inline', 'imageStyle:block',
            'imageStyle:side', 'resizeImage:original', 'resizeImage:S1', 'resizeImage:S2', 'resizeImage:S3',
            'resizeImage:L1', 'resizeImage:L2',
          ]
        },
        table: {
          contentToolbar: ["tableColumn", "tableRow", "mergeTableCells", "tableCellProperties", "tableProperties"]
        },
        codeBlock: {
          languages: [{
              language: 'cpp',
              label: 'C++',
              class: 'cpp'
            }, // The default language.
            // { language: 'c', label: 'C' },  // 默认class为language-c
            {
              language: 'java',
              label: 'Java'
            },
            {
              language: 'python',
              label: 'Python'
            },
            {
              language: 'plaintext',
              label: 'Plain text'
            },
          ]
        },
        link: {
          decorators: {
            isExternal: {
              mode: 'manual',
              label: 'Open in a new tab',
              defaultValue: true,
              attributes: {
                target: '_blank',
                rel: 'noopener noreferrer'
              }
            }
          }
        }
        // licenseKey: '',
      }

      // 初始化ckeditor5
      ClassicEditor.create(document.querySelector("#{{ $domId }}"), ck5_config).then(editor => {
        // 预览功能
        function refresh_preview(dom) {
          $(dom).html(editor.getData())
          window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub, document.getElementById(
            "preview_{{ $domId }}")]) // 渲染公式
        }

        // 初始化一个预览窗口
        let preview = $(
          '<div id="preview_{{ $domId }}" class="px-2 border ck-content" style="padding-top:1rem;flex:1; @if (!$preview) display:none; @endif"></div>'
        )
        preview.insertAfter($("#div_{{ $domId }} .ck-editor__editable"))
        refresh_preview(preview) // 初始预览一次

        // 内容改变时及时更新实体字段
        editor.model.document.on('change:data', function() {
          document.getElementById("{{ $domId }}").value = editor.getData()
          refresh_preview(preview) // 刷新预览
        });

        // 全局记住当前editor，方便外部使用该editor
        window["{{ $name }}"] = editor
        window["{{ $name }}_preview"] = preview
      }).catch(error => {
        console.error(error);
      })
    })
  </script>
</div>
