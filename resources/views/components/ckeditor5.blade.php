<div>
  <style>
    .ck-editor__main {
      /* 子元素并排 */
      display: flex;
    }

    .ck-source-editing-area,
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
  <div class="position-relative">
    <div id="div_{{ $domId }}">
      <textarea id="{{ $domId }}" name="{{ $name }}" rows="10" style="width:50%;flex:1">{{ $content }}</textarea>
    </div>
    <a href="javascript:" class="position-absolute" style="top: 2.6rem; right: 0.2rem; font-size:0.6rem"
      onclick="$('#preview_{{ $domId }}').toggle()">预览</a>
  </div>

  {{-- 生成编辑器。前提：务必在布局中引入ckeditor.js --}}
  <script>
    $(function() {
      // =========================== 预览功能 ============================
      // 定义函数：刷新预览区样式
      function refresh_preview(dom, text) {
        dom.html(text)
        window.MathJax.Hub.Queue(["Typeset", window.MathJax.Hub,
          document.getElementById("preview_{{ $domId }}")
        ]) // 渲染公式
      }

      // ============== markdown模式(预留未开发)===============
      let is_md = false
      if (is_md) {
        $("#div_{{ $domId }}").css('display', 'flex')
        let preview = $(
          '<div id="preview_{{ $domId }}" class="px-2 border" style="flex:1"></div>'
        )
        preview.insertAfter($("#{{ $domId }}"))

        refresh_preview(preview, marked.parse($("#{{ $domId }}").val())) // 初始预览一次
        $("#{{ $domId }}").on('input', function() {
          refresh_preview(preview, marked.parse($("#{{ $domId }}").val())) // 刷新预览
        });
        return
      }


      // ============== ckeditor5模式 ===============
      // ckeditor5配置
      const ck5_config = {
        removePlugins: ['Markdown'],
        language: 'zh-cn',
        ckfinder: {
          uploadUrl: "{{ route('api.ckeditor_files') }}"
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
              value: '25',
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
              language: "plaintext",
              label: "Plain text"
            },
            {
              language: "c",
              label: "C"
            },
            {
              language: "cpp",
              label: "C++"
            },
            {
              language: "java",
              label: "Java"
            },
            {
              language: "python",
              label: "Python"
            },
            {
              language: "php",
              label: "PHP"
            },
            {
              language: "cs",
              label: "C#"
            },
            {
              language: "html",
              label: "HTML"
            },
            {
              language: "javascript",
              label: "JavaScript"
            },
            {
              language: "css",
              label: "CSS"
            },
            {
              language: "diff",
              label: "Diff"
            },
            {
              language: "ruby",
              label: "Ruby"
            },
            {
              language: "typescript",
              label: "TypeScript"
            },
            {
              language: "xml",
              label: "XML"
            }
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
        let preview = $(
          '<div id="preview_{{ $domId }}" class="px-2 border ck-content" style="padding-top:1rem;flex:1; @if (!$preview) display:none; @endif"></div>'
        )
        preview.insertAfter($("#div_{{ $domId }} .ck-editor__editable"))

        refresh_preview(preview, editor.getData()) // 初始预览一次

        // 内容改变时及时更新实体字段
        editor.model.document.on('change:data', function() {
          document.getElementById("{{ $domId }}").value = editor.getData()
          refresh_preview(preview, editor.getData()) // 刷新预览
        });

        // 全局记住当前editor，方便外部使用该editor
        if (window.ck == undefined)
          window.ck = {}
        window.ck["{{ $name }}"] = editor
      }).catch(error => {
        console.error(error);
      })
    })
  </script>
</div>
