<div>
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
  <textarea id="{{ $domId }}" name="{{ $name }}">{{ $content }}</textarea>

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
        // 全局记住当前editor，方便外部使用该editor
        window["{{ $name }}"] = editor
        // 内容改变时及时更新实体字段
        editor.model.document.on('change:data', function() {
          document.getElementById("{{ $domId }}").value = editor.getData()
        });
      }).catch(error => {
        console.error(error);
      })

    })
  </script>
</div>
